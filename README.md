
# AlipayVoicePHP

## 项目简介 (Introduction)

AlipayVoicePHP 是一个纯 PHP 实现的支付宝到账语音播报接口。它接收金额参数，自动将数字转换为中文大写金额，并动态拼接项目内置的 TTS 音频片段，最终向客户端返回一段完整的 MP3 音频流。

AlipayVoicePHP is a pure PHP audio stream generator for Alipay voice broadcasts. It receives an amount parameter, automatically converts the number to Chinese uppercase characters, dynamically concatenates pre-recorded TTS audio clips, and returns a complete MP3 audio stream to the client.

## 项目文件结构 (Directory Structure)

```text
├── data/
│   ├── alipay_tts_result.zip  # 音频片段打包文件
│   └── tts/                   # TTS 音频素材目录
│       ├── diaoluo_da.mp3     # “支付宝到账”基础语音
│       ├── tts_success.mp3    # 播报提示音
│       └── tts_0.mp3 ... 等   # 数字与单位的发音片段
├── index.php                  # 核心接口代码文件
├── LICENSE                    # GPL-3.0 开源协议
└── README.md                  # 项目说明文档
