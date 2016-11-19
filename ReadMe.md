
#Theme
This is a developping LINE BOT application.
I would like you Chinese and Japanese to learn each language more effectively by using LINE.
The most important thing in Learning Chinese is to see a lot of hanzi I think. It is also important for learning Japanese to see a lot of Japanse strings.
So we support you to see and learn many text by multi-output way.

# How to Use
This is not for reuse but some parts of this app are good for another apps.
Follow LICENSE.md.

# MainFunction

- Translate Hanzi to phonetic sign like bopomofo(Taiwan), pinyin(China Mainland),IPA.
- As input methods, This app supports image, voice, location, URL and text.
- As output methods, also supports image,voice,URL,stickers and text.

# ToDo

### 画像認識 ImageCognition
- [x] 漢字を抜き出す [hanzi_cognition](/ImageCognition/hanzi_cognition.php)
  改善必要
- [ ] 日本語を抜き出す japanese_cognition
- [ ] 物体を抜き出す things_cognition

### 音声認識 VoiceCognition
- [ ] 音声を認識する voice_cognition

### 言語判別 DistinctLanguage
- [ ] 文字列から言語を見分ける（プロフィール，音声入力結果）distinct_language

### 位置情報名取得 GetAddress
- [x] 地名を取得 get_address

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
文字コードをキーに，各読み方などの情報をデータベースとして記録する方針．
下記プログラムはそれを参照する．
- [x] 漢字からピンイン [hanzi_pinyin](/HanziPronunciation/hanzi_pinyin.php)
- [ ] ピンインから注音 pinyin_bpmf
	- [ ] 漢字から注音 hanzi_bpmf
- [ ] ピンインから発音記号 pinyin_ipa
	- [ ] 漢字から発音記号 hanzi_bpmf
- [ ] 多音字を処理する multi_readable

### 音声合成 GenerateVoice
- [ ] 中国語生成 generate_chinese
- [ ] 日本語生成 generate_japanese

### PDF画像化 PDFToImage
- [ ] PDFを画像にする pdf_to_image

### 画像生成 GenerateImage
- [ ] 文字列解析結果 analyzed_image
- [ ] 学習：テスト testing_image
- [ ] 学習：新出漢字 somehanzi_image

### 形態素解析 MorphologicalAnalysis
- [ ] 形態素解析結果を返却 orphological_analysis
[楽天MA](https://github.com/rakuten-nlp/rakutenma)

### 量詞 CounterWord
- [ ] 名詞に対して量詞（数助詞）を返却する

### 文字列整形 StringFormatting
- [ ] 出力に応じて整形 string_formatting

### ひらがな化 JapaneseToHiragana
- [x] ひらがなAPIを使う [japanese_to_hiragana](/JapaneseToHiragana/japanese_to_hiragana.php)

### 代表漢字選別 EligibleChars
- [ ] 学ぶべき漢字をサジェスト eligible_chars

### 画像加工 ImageTreatment
- [ ] Imagickで画像に解析結果を埋め込む embed_string

### スタンプ選択 SelectSticker
- [ ] 該当するイメージのスタンプをランダムで出力 select_sticker

# 使用API
- gooひらがな化API

![URL](https://u.xgoo.jp/img/sgoo.png "gooロゴ")

# 利用データ
## [漢字データベース](http://kanji-database.sourceforge.net/index.html)
## [Unihan Database](http://unicode.org/charts/unihan.html)
