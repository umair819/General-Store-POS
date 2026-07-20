<?php
/**
 * TijaratPro POS — License Manager
 * Handles PC ID generation, trial verification, activation, and server handshakes.
 */

define('SECRET_KEY', 'TechBrainSecretKey12345678901234'); // 32 bytes key for XOR obfuscation
define('SERVER_URL', 'http://localhost:8000'); // Central licensing server URL

// Path configs
function get_app_data_dir() {
    $home = getenv('HOME');
    $appdata = getenv('APPDATA');
    if (PHP_OS_FAMILY === 'Windows' && $appdata) {
        $base = $appdata;
    } elseif (PHP_OS_FAMILY === 'Darwin' && $home) {
        $base = $home . '/Library/Application Support';
    } else {
        $base = $home ?: sys_get_temp_dir();
    }
    $dir = $base . '/TijaratPro';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return $dir;
}

function get_license_file_path() {
    return get_app_data_dir() . '/license.dat';
}

function get_trial_file_path() {
    return get_app_data_dir() . '/trial.dat';
}

// Encryption helpers
function _obfuscate($data) {
    $json = json_encode($data);
    $out = '';
    $keyLength = strlen(SECRET_KEY);
    for ($i = 0; $i < strlen($json); $i++) {
        $out .= $json[$i] ^ SECRET_KEY[$i % $keyLength];
    }
    return base64_encode($out);
}

function _deobfuscate($encrypted) {
    $json = base64_decode($encrypted);
    if ($json === false) return null;
    $out = '';
    $keyLength = strlen(SECRET_KEY);
    for ($i = 0; $i < strlen($json); $i++) {
        $out .= $json[$i] ^ SECRET_KEY[$i % $keyLength];
    }
    return json_decode($out, true);
}

function _write_encrypted_file($path, $data) {
    return file_put_contents($path, _obfuscate($data));
}

function _read_encrypted_file($path) {
    if (!file_exists($path)) return null;
    return _deobfuscate(file_get_contents($path));
}

// PC ID Generation
function get_pc_id() {
    $machine_guid = null;
    if (PHP_OS_FAMILY === 'Windows') {
        $output = @shell_exec('reg query "HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography" /v MachineGuid');
        if ($output && preg_match('/MachineGuid\s+REG_SZ\s+(\S+)/i', $output, $matches)) {
            $machine_guid = $matches[1];
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $output = @shell_exec('ioreg -rd1 -c IOPlatformExpertDevice');
        if ($output && preg_match('/"IOPlatformUUID"\s+=\s+"([^"]+)"/i', $output, $matches)) {
            $machine_guid = $matches[1];
        }
    }
    
    if (!$machine_guid) {
        $hostname = @gethostname() ?: 'unknown';
        $mac = '00-00-00-00-00-00';
        if (PHP_OS_FAMILY === 'Windows') {
            $output = @shell_exec('getmac');
            if ($output && preg_match('/([0-9A-F]{2}[:-]){5}([0-9A-F]{2})/i', $output, $matches)) {
                $mac = $matches[0];
            }
        } else {
            $output = @shell_exec('ifconfig || ip link');
            if ($output && preg_match('/([0-9a-f]{2}[:-]){5}([0-9a-f]{2})/i', $output, $matches)) {
                $mac = $matches[0];
            }
        }
        $machine_guid = $mac . '-' . $hostname;
    }
    
    return strtoupper(substr(hash('sha256', $machine_guid), 0, 16));
}

// Network helpers
function is_online() {
    $connected = @fsockopen("www.google.com", 80, $errno, $errstr, 2);
    if ($connected) {
        fclose($connected);
        return true;
    }
    return false;
}

function post_json($url, $data) {
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 5
        ]
    ];
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    if ($result === false) {
        return null;
    }
    return json_decode($result, true);
}

// Core Operations
function init_trial() {
    $pc_id = get_pc_id();
    $trialFile = get_trial_file_path();
    
    $data = _read_encrypted_file($trialFile);
    if ($data && isset($data['pc_id']) && $data['pc_id'] === $pc_id) {
        return $data;
    }
    
    $start = time();
    $expiry = $start + (15 * 24 * 60 * 60); // 15 days
    $data = [
        'pc_id' => $pc_id,
        'start' => date('Y-m-d', $start),
        'expiry' => date('Y-m-d', $expiry),
        'plan' => 'trial'
    ];
    _write_encrypted_file($trialFile, $data);
    return $data;
}

function extend_trial_period($days = 15) {
    $pc_id = get_pc_id();
    $trialFile = get_trial_file_path();
    $start = time();
    $expiry = $start + ($days * 24 * 60 * 60);
    $data = [
        'pc_id' => $pc_id,
        'start' => date('Y-m-d', $start),
        'expiry' => date('Y-m-d', $expiry),
        'plan' => 'trial'
    ];
    _write_encrypted_file($trialFile, $data);
}

function activate_license($key, $customer_name = '') {
    $key = strtoupper(trim($key));
    $pc_id = get_pc_id();
    $licenseFile = get_license_file_path();
    
    // 1. Emergency Offline Keys
    if ($key === 'MASTER-UNLOCK-ERP-2026' || str_replace('-', '', $key) === 'MASTERUNLOCKERP2026') {
        $expiry = date('Y-m-d', strtotime('+30 years'));
        $data = [
            'key' => $key,
            'pc_id' => $pc_id,
            'customer_name' => $customer_name ?: 'Emergency Master License',
            'activated_at' => date('Y-m-d'),
            'expires_at' => $expiry,
            'last_verified' => date('Y-m-d'),
            'plan' => 'lifetime'
        ];
        _write_encrypted_file($licenseFile, $data);
        return ['success' => true, 'message' => 'Master bypass activation successful!', 'plan' => 'lifetime'];
    }
    
    if ($key === 'MASTER-EXTEND-15-DAYS' || str_replace('-', '', $key) === 'MASTEREXTEND15DAYS') {
        extend_trial_period(15);
        if (file_exists($licenseFile)) {
            @unlink($licenseFile);
        }
        return ['success' => true, 'message' => 'Trial extended for another 15 days successfully!', 'plan' => 'trial'];
    }
    
    // 2. Standard Key Online Activation
    if (!is_online()) {
        return ['success' => false, 'message' => 'Internet connection is required to activate your license key.'];
    }
    
    $url = SERVER_URL . '/api/license/activate';
    $payload = [
        'license_key' => $key,
        'machine_id' => $pc_id,
        'device_name' => 'TijaratPro Client',
        'os_info' => PHP_OS,
        'app_version' => '1.0.0'
    ];
    
    $res = post_json($url, $payload);
    if ($res && isset($res['status']) && in_array($res['status'], ['activated', 'already_active'])) {
        $expires_raw = $res['expires_at'] ?? '';
        $expires_clean = $expires_raw ? substr($expires_raw, 0, 10) : date('Y-m-d', strtotime('+10 years'));
        
        $data = [
            'key' => $key,
            'pc_id' => $pc_id,
            'customer_name' => $customer_name ?: 'TijaratPro Customer',
            'activated_at' => date('Y-m-d'),
            'expires_at' => $expires_clean,
            'last_verified' => date('Y-m-d'),
            'plan' => 'lifetime'
        ];
        _write_encrypted_file($licenseFile, $data);
        return ['success' => true, 'message' => 'Activation successful!', 'plan' => 'lifetime'];
    }
    
    $error_msg = $res['detail'] ?? ($res['message'] ?? 'Activation failed. Please check key or contact support.');
    return ['success' => false, 'message' => $error_msg];
}

function check_license() {
    $pc_id = get_pc_id();
    $licenseFile = get_license_file_path();
    
    // 1. Check Lifetime License
    if (file_exists($licenseFile)) {
        $data = _read_encrypted_file($licenseFile);
        if ($data) {
            // Hardware match
            if (!isset($data['pc_id']) || $data['pc_id'] !== $pc_id) {
                return ['status' => 'invalid', 'message' => 'Hardware mismatch: License belongs to another PC.', 'days_left' => 0];
            }
            
            // Expiry
            $expires = strtotime($data['expires_at'] ?? '');
            if ($expires && time() > $expires) {
                return ['status' => 'invalid', 'message' => 'License expired. Please renew.', 'days_left' => 0];
            }
            
            // Periodic verification (every 3 days silent check, 7 days offline grace)
            $last_verified = isset($data['last_verified']) ? strtotime($data['last_verified']) : time();
            $days_since_verify = floor((time() - $last_verified) / (24 * 60 * 60));
            
            if ($days_since_verify >= 3) {
                if (is_online()) {
                    $url = SERVER_URL . '/api/license/verify';
                    $payload = ['license_key' => $data['key'], 'machine_id' => $pc_id];
                    $res = post_json($url, $payload);
                    if ($res && isset($res['status']) && $res['status'] === 'valid') {
                        $data['last_verified'] = date('Y-m-d');
                        _write_encrypted_file($licenseFile, $data);
                    }
                } elseif ($days_since_verify > 7) {
                    return ['status' => 'invalid', 'message' => 'Offline grace period exceeded. Please connect to internet to verify license.', 'days_left' => 0];
                }
            }
            
            return ['status' => 'valid', 'message' => 'License active & verified.', 'days_left' => 9999];
        }
    }
    
    // 2. Check Trial License
    $trialData = init_trial();
    if ($trialData) {
        $expiry = strtotime($trialData['expiry']);
        $days_left = ceil(($expiry - time()) / (24 * 60 * 60));
        if ($days_left <= 0) {
            return ['status' => 'invalid', 'message' => 'Trial period expired. Please enter a license key to continue.', 'days_left' => 0];
        }
        return ['status' => 'trial', 'message' => "Trial active. $days_left days remaining.", 'days_left' => $days_left];
    }
    
    return ['status' => 'invalid', 'message' => 'No active license or trial found.', 'days_left' => 0];
}

function deactivate_license() {
    $pc_id = get_pc_id();
    $licenseFile = get_license_file_path();
    if (!file_exists($licenseFile)) {
        return false;
    }
    
    $data = _read_encrypted_file($licenseFile);
    if (!$data) return false;
    
    if (is_online()) {
        $url = SERVER_URL . '/api/license/deactivate';
        $payload = ['license_key' => $data['key'], 'machine_id' => $pc_id];
        post_json($url, $payload);
    }
    
    @unlink($licenseFile);
    return true;
}
