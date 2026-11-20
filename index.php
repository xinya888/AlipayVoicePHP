<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    returnResponse(-405, '不支持的请求方法，仅支持POST和GET');
}
date_default_timezone_set('Asia/Shanghai');
initSystem();
$input = getAndValidateInput();
$chineseAmount = convertToEnterpriseChequeAmount($input);
$audioSequence = buildEnterpriseAudioSequence($chineseAmount, $input);
outputFinalAudio($audioSequence);
function initSystem() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
        returnError("仅支持GET或POST请求");
    }
}
function getAndValidateInput() {
    $input = isset($_REQUEST['msg']) ? trim($_REQUEST['msg']) : '';
    if (!preg_match('/^[0-9.]+$/', $input)) {
        returnError("金额格式错误，只能包含数字和小数点");
    }
    return formatEnterpriseNumber($input);
}
function formatEnterpriseNumber($input) {
    $num = preg_replace('/[^0-9.]/', '', $input);
    if (!is_numeric($num)) {
        returnError("无效的金额格式");
    }
    if (empty($num)) $num = "0";
    if (strpos($num, '.') !== false) {
        $parts = explode('.', $num);
        $num = $parts[0] . '.' . (isset($parts[1]) ? substr($parts[1], 0, 2) : '00');
    }
    return number_format((float)$num, 2, '.', '');
}
function convertToEnterpriseChequeAmount($num) {
    $units = ['', '万', '亿'];
    $subUnits = ['', '十', '百', '千'];
    $digits = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
    list($integer, $decimal) = explode('.', $num);
    $result = '';
    $integer = ltrim($integer, '0');
    if ($integer === '') $integer = '0';
    $length = strlen($integer);
    $zeroCount = 0;
    $needSpecialTen = ($length > 2 && $integer[-$length + 1] == '1');
    for ($i = 0; $i < $length; $i++) {
        $digit = $integer[$i];
        $pos = $length - $i - 1;
        if ($digit != '0') {
            if ($needSpecialTen && $pos % 4 == 1 && $digit == '1' && $i == 1) {
                $result .= $digits[$digit];
            } elseif (!($digit == '1' && $pos % 4 == 1 && $length <= 2)) {
                $result .= $digits[$digit];
            }
        } else {
            $zeroCount++;
        }
        if ($digit != '0' || ($pos % 4 == 0 && $zeroCount < 4)) {
            $subUnitPos = $pos % 4;
            $mainUnitPos = floor($pos / 4);
            
            if ($digit != '0' || ($pos % 4 == 0 && $zeroCount < 4)) {
                if (!($digit == '1' && $subUnitPos == 1 && $length <= 2)) {
                    if ($digit != '0' || $subUnitPos == 0) {
                        $result .= $subUnits[$subUnitPos];
                    }
                } else {
                    $result .= $subUnits[$subUnitPos];
                }
            }
            if ($subUnitPos == 0 && isset($units[$mainUnitPos])) {
                $result .= $units[$mainUnitPos];
            }
        }
    }
    if ($decimal != '00') {
        $result .= '点';
        for ($i = 0; $i < strlen($decimal); $i++) {
            $result .= $digits[$decimal[$i]];
        }
    }
    $result = preg_replace('/零+/', '零', $result);
    $result = preg_replace('/零(万|亿)/', '$1', $result);
    
    return $result . '元';
}
function buildEnterpriseAudioSequence($chineseAmount, $originalInput) {
    $audioMap = [
        '零' => 'tts_0.mp3',
        '一' => 'tts_1.mp3',
        '二' => 'tts_2.mp3',
        '三' => 'tts_3.mp3',
        '四' => 'tts_4.mp3',
        '五' => 'tts_5.mp3',
        '六' => 'tts_6.mp3',
        '七' => 'tts_7.mp3',
        '八' => 'tts_8.mp3',
        '九' => 'tts_9.mp3',
        '十' => 'tts_ten.mp3',
        '百' => 'tts_hundred.mp3',
        '千' => 'tts_thousand.mp3',
        '万' => 'tts_ten_thousand.mp3',
        '亿' => 'tts_ten_million.mp3',
        '点' => 'tts_dot.mp3',
        '元' => 'tts_yuan.mp3',
    ];
    $sequence = [
        getAudioPath('diaoluo_da.mp3'),
        getAudioPath('tts_success.mp3')
    ];
    preg_match_all('/./u', $chineseAmount, $matches);
    foreach ($matches[0] as $char) {
        if (isset($audioMap[$char])) {
            $sequence[] = getAudioPath($audioMap[$char]);
        }
    }
    
    return $sequence;
}
function getAudioPath($filename) {
    $path = __DIR__ . '/data/tts/' . $filename;
    if (!file_exists($path)) {
        returnError("缺少必要的音频文件: " . $filename);
    }
    return $path;
}
function outputFinalAudio($sequence) {
    header("Content-Type: audio/mpeg");
    header("Cache-Control: no-store");
    
    foreach ($sequence as $file) {
        readfile($file);
    }
    
    exit;
}
function returnError($message) {
    $errorData = [
        'error' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s'),
        'info' => '芯云接口平台提供支持',
        'code' => -200
    ];
    
    header('Content-Type: application/json');
    die(json_encode($errorData, JSON_UNESCAPED_UNICODE));
}
?>