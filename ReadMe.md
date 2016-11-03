
#Theme
This is a developping LINE BOT application.
I would like you Chinese and Japanese to learn each language more effectively by using LINE.
The most import thing in Learning Chinese is to see more hanzi I think. It is also important for learning Japanese to see more and more Japanse strings.
So we support you to see and learn many text by multi-output way.

# How to Use
This is not for reuse but some parts of this app are good for another apps.
Follow LICENSE.md.

# MainFunction

- Translate Hanzi to phonetic sign like bopomofo(Taiwan), pinyin(China Mainland),IPA.
- As input methods, This app supports Image, voice, GPS, URL and text.
- As output methods, This app also supports Image,voice,URL,stickers and text.

# ToDo

### 画像認識 ImageCognition
- [ ] 漢字を抜き出す hanzi_cognition
- [ ] 日本語を抜き出す japanese_cognition
- [ ] 物体を抜き出す things_cognition

### 音声認識 VoiceCognition
- [ ] 音声を認識する voice_cognition

### 言語判別 DistinctLanguage
- [ ] 文字列から言語を見分ける（プロフィール，音声入力結果）distinct_language

### 位置情報名取得 GetAddress
- [ ] 地名を取得 get_address
- [ ] 最寄りの駅名を取得（日本）get_station

### ウェブサイトから漢字取得 GetWebPage
- [ ] URLをもとにページの文字列を取得 get_web_page

### データベース登録 DatabaseMethods
- [ ] 届いたメッセージarrived_message
- [ ] 送信したメッセージsent_message
- [ ] 漢字覚えているかどうか，リマインド回数など learning_hanzi
- [ ] 漢字データベース読み込み hanzi_base
- [ ] 漢字データベース修正 hanzi_base_mod

### 漢字辞書 ShowDict
- [ ] 簡体字辞書へのリンク show_simple 
- [ ] 繁体字辞書へのリンク show_trad
(オープンデータなら文字列で語釈を返却)

### 繁体字簡体字変換 TransHanzi
- [ ] 指定した漢字に変換 trans_hanzi

### 漢字読み変換 HanziPronunciation
- [ ] ピンイン hanzi_pinyin
- [ ] 注音 hanzi_bpmf
- [ ] 発音記号 hanzi_ipa

### 音声合成 GenerateVoice
- [ ] 中国語 generate_chinese
- [ ] 日本語 generate_japanese

### PDF画像化 PDFToImage
- [ ] PDFを画像にする pdf_to_image

### 画像生成 GenerateImage
- [ ] 文字列解析結果 analyzed_image
- [ ] 学習：テスト testing_image
- [ ] 学習：新出漢字 somehanzi_image

### 形態素解析 MorphologicalAnalysis
- [ ] 形態素解析結果を返却 orphological_analysis

### 文字列整形 StringFormatting
- [ ] 出力に応じて整形 string_formatting

### ひらがな化 JapaneseToHiragana
- [ ] ひらがなAPIを使う Japanese_to_hiragana

### 代表漢字選別 EligibleChars
- [ ] 学ぶべき漢字をサジェスト eligible_chars

### 画像加工 ImageTreatment
- [ ] Imagickで画像に解析結果を埋め込む embed_string

### スタンプ選択 SelectSticker
- [ ] 該当するイメージのスタンプをランダムで出力 select_sticker