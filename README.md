jothon donate bot
=================

這是為了 2020-05-23 的 g0v 黑客松所開發的抖內機器人後端程式，因為是為了當時大松所開發，因此程式內容是針對 g0v 大松環境所寫死（像是信件解析格式是針對 netiCRM 的信件格式，有 slack 通知揪松的非公開討論群組等...）如果有需要針對自己情境需要另外改寫，這邊會說明各檔案用處。

另外程式以外的說明（Ex: 為什麼會要有這機器人程式，Amazon Web Service 如何設定...），記錄在 [hackmd 共筆](https://g0v.hackmd.io/ETDHDKgRS1eNOR1QiFAK4Q)

授權
----
* 程式以 BSD License 授權

檔案說明
--------
* index.php
  * Amazon Web Service 的 SNS 設定的 endpoint ，主要功用是收到 email 時解出 email 內容
  * 這程式會做以下事情
    * 收到信件的話，把信件內容丟進 /tmp/tmp 內，以便偵錯使用
    * 如果確定是捐款通知信的話，將捐款資訊以 JSON 格式寫入 /tmp/donates 內（只會寫入最新十筆）
    * 如果確定是捐款通知信的話，將捐款資訊透過 slack 通知至揪松的 slack 頻道
  * 這個網址請盡量只給 AWS SNS 使用，請勿公開，以免被人偽造捐款通知。
* donate-api.php
  * 將 /tmp/donates 內最新十筆捐款記錄以 CORS API 的型式輸出給前端使用
  * [g0v/donate-checker](https://github.com/g0v/donate-checker) 是一個前端介面
    * 可以用 https://g0v.github.io/donate-checker/index.html?url=https://your-domain/donate-api.php 讓前端介面顯示你的捐款資訊
      * 以上 your-domain 請替換成你自己架設的網域
    * 前端會每五秒去 API 查詢是否有新捐款，並以動畫型式呈現
    * 前端頁面主要設計是給 OBS 直播用，請盡量不要直接嵌入在對外網站上，以免太多人瀏覽會造成後端存取量過大，放在對外網站也可能造成 index.php 網址外洩造成別人可偽造捐款通知
* fake-donate.php
  * 給測試前端的人專用的產生假 donate 資料，確定通知是否正常顯示用的，會將假資料寫入 /tmp/fake-donates 中
  * 可以使用 https://jothon-donate-test.ronny.tw/fake-donate.php 測試
* fake-donate-api.php
  * 與 donate-api.php 一樣輸出最新十筆捐款記錄，不過是 /tmp/fake-donates 下的假資料
  * 在 https://g0v.github.io/donate-checker/index.html 測試使用，可以開著 https://g0v.github.io/donate-checker/index.html 頁面，然後在 https://jothon-donate-test.ronny.tw/fake-donate.php 輸入假資料看看會不會有抖內資訊顯示
