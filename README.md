# Mails
Mails Plugin for NetCommons

[![Build Status](https://travis-ci.org/NetCommons3/Mails.svg?branch=master)](https://travis-ci.org/NetCommons3/Mails)
[![Coverage Status](https://img.shields.io/coveralls/NetCommons3/Mails.svg)](https://coveralls.io/r/NetCommons3/Mails?branch=master)

| dependencies | status |
| ------------ | ------ |
| composer.json | [![Dependency Status](https://www.versioneye.com/user/projects/5665251b846d41000a000471/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5665251b846d41000a000471) |

### [phpdoc](https://netcommons3.github.io/NetCommons3Docs/phpdoc/Mails/)

* [自動セットする埋め込みタグ一覧](https://netcommons3.github.io/NetCommons3Docs/phpdoc/Mails/classes/NetCommonsMailAssignTag.html)

### [メール機能の組み込み](https://github.com/NetCommons3/NetCommons3/wiki/メール組み込み)

### メールのcron設定

未来日メールやリマインダーを送るためには、cron設定が必要です。<br />
設定しない場合、未来日メールやリマインダーは送りません。<br />

#### 設定例 - ubuntsu12.04LTS

/etc/crontab
```
# nc3 reminder mail
*/5 *	* * *	apache	cd /var/www/app/app && Console/cake Mails.mailSend
```

参考URL: http://book.cakephp.org/2.0/ja/console-and-shells/cron-jobs.html

