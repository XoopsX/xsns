オリジナルのxsns-1.1.1からの、ハック内容一覧

- 2011/ 9/23
-- コメント編集のたびに、notificationが飛ばないようにした。
-- ブロックやd3pipesで、SQL文の不具合で最新記事本文が引けないのを修正。
 　http://www.naaon.com/modules/plactice/index.php/home/hobby/xoops/xsns_d3pipes.htm
　
-- 携帯対応でmultipart-form-data外すとXoopsMultiTokenHandlerをソースに追記しないと
　　いけない件の対応
　　http://www.mc8.jp/HD/modules/xpwiki/38.html#m375deb8
-- その他