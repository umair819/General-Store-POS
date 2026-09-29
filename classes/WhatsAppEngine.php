<?php
// ============================================================
// Tijarat PRO — WhatsApp Engine Abstraction Class
// Supports:
// 1. Baileys Local Node Service (O2, O3, H1, H2, H3)
// 2. Evolution API REST Docker Service (H3 Omni-Channel)
// 3. WhatsApp Direct Web Link (O1 / Fallback)
// ============================================================

class WhatsAppEngine {
    private static $baileysPort = 9001;
    private static $baileysHost = '127.0.0.1';

    /**
     * Format phone number to clean Pakistani / international format without + or spaces
     */
    public static function formatPhone($phone) {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($clean, '0') === 0 && strlen($clean) === 11) {
            $clean = '92' . substr($clean, 1);
        }
        return $clean;
    }

    /**
     * Check if Baileys background service on port 9001 is running
     */
    public static function isBaileysActive() {
        $fp = @fsockopen(self::$baileysHost, self::$baileysPort, $errno, $errstr, 1);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * Get connection status of active engine
     */
    public static function getStatus() {
        if (!hasFeature('whatsapp_baileys')) {
            return [
                'status' => 'unsupported_package',
                'engine' => 'link',
                'connected' => false,
                'message' => 'WhatsApp automation is locked in this package edition.'
            ];
        }

        $activePackage = getActivePackage();
        $isEvolution = ($activePackage === 'H3' && getConfig('whatsapp_mode') === 'evolution_api');

        if ($isEvolution) {
            $evoUrl = getConfig('evolution_api_url', 'http://127.0.0.1:8080');
            $evoKey = getConfig('evolution_api_key', '');
            $instance = getConfig('evolution_instance_name', 'tijarat');

            $ch = curl_init("$evoUrl/instance/connectionState/$instance");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $evoKey"]);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($res, true);
            $connected = ($httpCode === 200 && ($data['state'] ?? '') === 'open');

            return [
                'status' => $connected ? 'connected' : 'disconnected',
                'engine' => 'evolution_api',
                'connected' => $connected,
                'state' => $data['state'] ?? 'closed',
                'message' => $connected ? 'Evolution API connected.' : 'Evolution API disconnected or instance offline.'
            ];
        }

        // Default: Baileys service check
        if (!self::isBaileysActive()) {
            return [
                'status' => 'service_offline',
                'engine' => 'baileys',
                'connected' => false,
                'message' => 'Baileys background node service is offline on port ' . self::$baileysPort
            ];
        }

        $ch = curl_init('http://' . self::$baileysHost . ':' . self::$baileysPort . '/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true) ?: [];
        $state = $json['state'] ?? 'DISCONNECTED';

        return [
            'status' => ($state === 'CONNECTED') ? 'connected' : 'connecting',
            'engine' => 'baileys',
            'connected' => ($state === 'CONNECTED'),
            'state' => $state,
            'user' => $json['user'] ?? null,
            'message' => ($state === 'CONNECTED') ? 'Baileys WhatsApp connected.' : 'QR scan required or service connecting.'
        ];
    }

    /**
     * Send a text message via active engine
     */
    public static function sendMessage($phone, $message) {
        $formattedPhone = self::formatPhone($phone);
        if (empty($formattedPhone)) {
            return ['success' => false, 'message' => 'Invalid phone number'];
        }

        $activePackage = getActivePackage();
        $isEvolution = ($activePackage === 'H3' && getConfig('whatsapp_mode') === 'evolution_api');

        // 1. Evolution API (H3)
        if ($isEvolution) {
            $evoUrl = getConfig('evolution_api_url', 'http://127.0.0.1:8080');
            $evoKey = getConfig('evolution_api_key', '');
            $instance = getConfig('evolution_instance_name', 'tijarat');

            $payload = [
                'number' => $formattedPhone,
                'textMessage' => ['text' => $message]
            ];

            $ch = curl_init("$evoUrl/message/sendText/$instance");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "apikey: $evoKey"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($res, true);
            if ($httpCode === 200 || $httpCode === 201) {
                return ['success' => true, 'engine' => 'evolution_api', 'data' => $data];
            }
            return ['success' => false, 'engine' => 'evolution_api', 'error' => $data['message'] ?? 'Evolution API error'];
        }

        // 2. Baileys Local Node Service (O2, O3, H1, H2)
        if (hasFeature('whatsapp_baileys') && self::isBaileysActive()) {
            $ch = curl_init('http://' . self::$baileysHost . ':' . self::$baileysPort . '/send-message');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'phone' => $formattedPhone,
                'message' => $message
            ]));
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($res, true);
            if ($httpCode === 200 && ($data['success'] ?? false)) {
                return ['success' => true, 'engine' => 'baileys', 'data' => $data];
            }
            return ['success' => false, 'engine' => 'baileys', 'error' => $data['error'] ?? 'Baileys failed to send'];
        }

        // 3. Fallback: Generate Direct WhatsApp URL
        $encodedMsg = urlencode($message);
        $waLink = "https://api.whatsapp.com/send?phone={$formattedPhone}&text={$encodedMsg}";

        return [
            'success' => true,
            'engine' => 'link',
            'link' => $waLink,
            'message' => 'WhatsApp link generated for manual click.'
        ];
    }

    /**
     * Build and send digital receipt message
     */
    public static function sendReceipt($phone, $invoiceData) {
        $shopName = getConfig('shop_name', 'Tijarat PRO');
        $currency = getConfig('shop_currency', 'PKR');
        $invNo = $invoiceData['invoice_no'] ?? 'INV-' . time();
        $total = number_format((float)($invoiceData['total'] ?? 0), 2);
        $paid = number_format((float)($invoiceData['paid_amount'] ?? 0), 2);
        $balance = number_format((float)($invoiceData['balance_amount'] ?? 0), 2);

        $msg = "🧾 *{$shopName}* — Digital Receipt\n";
        $msg .= "Invoice: *#{$invNo}*\n";
        $msg .= "Date: " . date('d-M-Y h:i A') . "\n";
        $msg .= "--------------------------------\n";

        if (!empty($invoiceData['items']) && is_array($invoiceData['items'])) {
            foreach ($invoiceData['items'] as $item) {
                $qty = $item['quantity'] ?? $item['qty'] ?? 1;
                $price = number_format((float)($item['sale_price'] ?? $item['price'] ?? 0), 2);
                $name = $item['name'] ?? $item['product_name'] ?? 'Item';
                $msg .= "• {$name} x{$qty} = {$currency} {$price}\n";
            }
            $msg .= "--------------------------------\n";
        }

        $msg .= "Total Amount: *{$currency} {$total}*\n";
        $msg .= "Paid Amount: {$currency} {$paid}\n";
        if ((float)($invoiceData['balance_amount'] ?? 0) > 0) {
            $msg .= "Balance Due (Udhaar): *{$currency} {$balance}*\n";
        }
        $msg .= "\nThank you for shopping with us! 🙏";

        return self::sendMessage($phone, $msg);
    }
}
