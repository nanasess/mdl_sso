<?php

class LC_Page_Sso_CustomerRegister extends LC_Page_AbstractSso
{
    /** @var string[] */
    public $arrPref;
    /** @var string[] */
    public $arrJob;
    /** @var string[] */
    public $arrReminder;
    /** @var string[] */
    public $arrCountry;
    /** @var string[] */
    public $arrSex;
    /** @var string[] */
    public $arrMAILMAGATYPE;
    /** @var int[] */
    public $arrYear;
    /** @var int[] */
    public $arrMonth;
    /** @var int[] */
    public $arrDay;
    /** @var int */
    public $max;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!is_object($this->httpClient)) {
            $this->httpClient = new GuzzleHttp\Client([
                'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
            ]);
        }
        $this->tpl_title = '会員登録';
        $masterData         = new SC_DB_MasterData_Ex();
        $this->arrPref      = $masterData->getMasterData('mtb_pref');
        $this->arrJob       = $masterData->getMasterData('mtb_job');
        $this->arrReminder  = $masterData->getMasterData('mtb_reminder');
        $this->arrCountry   = $masterData->getMasterData('mtb_country');
        $this->arrSex       = $masterData->getMasterData('mtb_sex');
        $this->arrMAILMAGATYPE = $masterData->getMasterData('mtb_mail_magazine_type');

        // 生年月日選択肢の取得
        $objDate            = new SC_Date_Ex(BIRTH_YEAR, date('Y'));
        $this->arrYear      = $objDate->getYear('', '1980', '');
        $this->arrMonth     = $objDate->getMonth(true);
        $this->arrDay       = $objDate->getDay(true);

        $this->httpCacheControl('nocache');
        // $this->setTemplate(realpath(__DIR__.'/../../templates/default/sso/register.tpl'));
        $this->setTemplate(realpath(__DIR__.'/../../templates/default/sso/register_kiyaku_only.tpl'));
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
    }
    public function action()
    {
        parent::action();

        $arrKiyaku = $this->lfGetKiyakuData();
        $this->max = count($arrKiyaku);
        $this->tpl_kiyaku_text = $this->lfMakeKiyakuText($arrKiyaku, $this->max, null);

        $objFormParam = new SC_FormParam_Ex();
        SC_Helper_Customer_Ex::sfCustomerEntryParam($objFormParam);
        $objFormParam->setParam($_POST);

        $password = SC_Utils_Ex::sfGetRandomString(16);
        $password .= 'abc123';

        switch ($this->getMode()) {
            case 'confirm':
                $this->setDummyFormParamTo($objFormParam);
                $this->arrErr = SC_Helper_Customer_Ex::sfCustomerEntryErrorCheck($objFormParam);

                // 入力エラーなし
                if (empty($this->arrErr)) {
                    $this->setTemplate(realpath(__DIR__.'/../../templates/default/sso/confirm.tpl'));
                }
                break;
            case 'complete':
                $this->setDummyFormParamTo($objFormParam, true);

                $this->arrErr = SC_Helper_Customer_Ex::sfCustomerEntryErrorCheck($objFormParam);
                if (empty($this->arrErr)) {
                    $this->setDummyFormCompleteParamTo($objFormParam);

                    $customer_id = SC_Helper_Customer_Ex::sfEditCustomerData($this->lfMakeSqlVal($objFormParam));

                    $this->lfSendMail($customer_id, $objFormParam->getHashArray());

                    // ログイン状態にする
                    $objCustomer = new SC_Customer_Ex();
                    $objCustomer->setLogin($objFormParam->getValue('email'));
                    $objCustomer->setOAuth2ClientId($_SESSION['token']['oauth2_client_id']);

                    $_SESSION['registered_customer_id'] = $customer_id;
                    $_SESSION['userinfo']['customer_id'] = $customer_id;
                    $_SESSION['token']['customer_id'] = $customer_id;
                    $userInfo = SC_Helper_OAuth2::registerUserInfo($_SESSION['userinfo']);
                    SC_Helper_OAuth2::registerToken($_SESSION['token']);
                    // 完了ページに移動させる。
                    SC_Response_Ex::sendRedirectFromUrlPath('sso/'.$this->short_name.'/complete');
                    SC_Response_Ex::actionExit();
                }
                break;
            default:
                $objFormParam->setValue('name01', $_SESSION['userinfo']['name']);
                $objFormParam->setValue('email', $_SESSION['userinfo']['email']);
        }

        $this->arrForm = $objFormParam->getFormParamList();
    }

    /**
     * 規約文の作成
     *
     * @param mixed $arrKiyaku
     * @param integer $max
     * @param mixed $offset
     * @access public
     * @return string 規約の内容をテキストエリアで表示するように整形したデータ
     */
    public function lfMakeKiyakuText($arrKiyaku, $max, $offset)
    {
        $this->tpl_kiyaku_text = '';
        for ($i = 0; $i < $max; $i++) {
            if ($offset !== null && ($offset - 1) <> $i) continue;
            $tpl_kiyaku_text.=$arrKiyaku[$i]['kiyaku_title'] . "\n\n";
            $tpl_kiyaku_text.=$arrKiyaku[$i]['kiyaku_text'] . "\n\n";
        }

        return $tpl_kiyaku_text;
    }

    /**
     * 規約内容の取得
     *
     * @access private
     * @return array $arrKiyaku 規約の配列
     */
    public function lfGetKiyakuData()
    {
        $objKiyaku = new SC_Helper_Kiyaku_Ex();
        $arrKiyaku = $objKiyaku->getList();

        return $arrKiyaku;
    }

    /**
     * 会員登録完了メール送信する
     *
     * @access private
     * @return void
     */
    public function lfSendMail($customer_id, $arrForm)
    {
        $CONF           = SC_Helper_DB_Ex::sfGetBasisData();

        $objMailText    = new SC_SiteView_Ex();
        $objMailText->setPage($this);
        $objMailText->assign('CONF', $CONF);
        $objMailText->assign('name01', $arrForm['name01']);
        $objMailText->assign('customer_id', $customer_id);
        $objMailText->assignobj($this);

        $objHelperMail  = new SC_Helper_Mail_Ex();
        $objHelperMail->setPage($this);

        $subject        = $objHelperMail->sfMakeSubject('会員登録のご完了');
        $toCustomerMail = $objMailText->fetch('mail_templates/customer_regist_mail.tpl');


        $objMail = new SC_SendMail_Ex();
        $objMail->setItem(
            '',                     // 宛先
            $subject,               // サブジェクト
            $toCustomerMail,        // 本文
            $CONF['email03'],       // 配送元アドレス
            $CONF['shop_name'],     // 配送元 名前
            $CONF['email03'],       // reply_to
            $CONF['email04'],       // return_path
            $CONF['email04'],       // Errors_to
            $CONF['email01']        // Bcc
        );
        // 宛先の設定
        $objMail->setTo($arrForm['email'],
                        $arrForm['name01'] . $arrForm['name02'] .' 様');

        $objMail->sendMail();
    }

    /**
     * 入力エラーのチェック.
     *
     * @param  array $arrRequest リクエスト値($_GET)
     * @return array $arrErr エラーメッセージ配列
     */
    public function lfCheckError($arrRequest)
    {
        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();
        // パラメーター情報の初期化
        $objFormParam->addParam('郵便番号1', 'zip01', ZIP01_LEN, 'n', array('EXIST_CHECK', 'NUM_COUNT_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('郵便番号2', 'zip02', ZIP02_LEN, 'n', array('EXIST_CHECK', 'NUM_COUNT_CHECK', 'NUM_CHECK'));
        // // リクエスト値をセット
        $arrData['zip01'] = $arrRequest['zip01'];
        $arrData['zip02'] = $arrRequest['zip02'];
        $objFormParam->setParam($arrData);
        // エラーチェック
        $arrErr = $objFormParam->checkError();

        return $arrErr;
    }

    /**
     * 会員登録に必要なSQLパラメーターの配列を生成する.
     *
     * フォームに入力された情報を元に, SQLパラメーターの配列を生成する.
     * モバイル端末の場合は, email を email_mobile にコピーし,
     * mobile_phone_id に携帯端末IDを格納する.
     *
     * @param SC_FormParam $objFormParam
     * @access private
     * @return array
     */
    public function lfMakeSqlVal(&$objFormParam)
    {
        $arrForm                = $objFormParam->getHashArray();
        $arrResults             = $objFormParam->getDbArray();

        // 生年月日の作成
        $arrResults['birth']    = SC_Utils_Ex::sfGetTimestamp($arrForm['year'], $arrForm['month'], $arrForm['day']);

        // 仮会員 1 本会員 2
        $arrResults['status']   = '2';

        /*
         * secret_keyは、テーブルで重複許可されていない場合があるので、
         * 本会員登録では利用されないがセットしておく。
         */
        $arrResults['secret_key'] = SC_Helper_Customer_Ex::sfGetUniqSecretKey();

        // 入会時ポイント
        $CONF = SC_Helper_DB_Ex::sfGetBasisData();
        $arrResults['point'] = $CONF['welcome_point'];

        return $arrResults;
    }

    /**
     * @param SC_FormParam_Ex $objFormParam
     * @param bool $is_complete
     */
    protected function setDummyFormParamTo(SC_FormParam_Ex $objFormParam, $is_complete = false)
    {
        // スペースが入っている場合があるため, エラーチェック前に除去する
        $name01 = $objFormParam->getValue('name01');
        $name01 = str_replace([" ", "　"], '', $name01);
        // XXX 登録完了時に姓名を分割したいので, 一旦 reminder_answer に入れておく
        if (!$is_complete) {
            $objFormParam->setValue('reminder_answer', $objFormParam->getValue('name01'));
        }
        $objFormParam->setValue('name01', $name01);
        $objFormParam->setValue('name02', '名');
        $objFormParam->setValue('kana01', 'セイ');
        $objFormParam->setValue('kana02', 'メイ');
        $objFormParam->setValue('email02', $objFormParam->getValue('email'));
        $objFormParam->setValue('zip01', '000');
        $objFormParam->setValue('zip02', '0000');
        $objFormParam->setValue('pref', '1');
        $objFormParam->setValue('addr01', '1');
        $objFormParam->setValue('addr02', '2');
        $objFormParam->setValue('tel01', '000');
        $objFormParam->setValue('tel02', '000');
        $objFormParam->setValue('tel03', '000');
        $objFormParam->setValue('sex', '0');
        $objFormParam->setValue('password', 'password123');
        $objFormParam->setValue('password02', 'password123');
        $objFormParam->setValue('reminder', 1);

    }

    /**
     * @param SC_FormParam_Ex $objFormParam
     */
    protected function setDummyFormCompleteParamTo(SC_FormParam_Ex $objFormParam)
    {
        // スペースが含まれる場合は姓名を分割する
        $name = $objFormParam->getValue('reminder_answer');
        list($name01, $name02) = explode(' ',  str_replace("　", ' ', $name));
        $objFormParam->setValue('name01', $name01);
        $objFormParam->setValue('name02', $name02);
        $objFormParam->setValue('kana01', null);
        $objFormParam->setValue('kana02', null);
        $objFormParam->setValue('email02', $objFormParam->getValue('email'));
        $objFormParam->setValue('zip01', null);
        $objFormParam->setValue('zip02', null);
        $objFormParam->setValue('pref', 0);
        $objFormParam->setValue('addr01', null);
        $objFormParam->setValue('addr02', null);
        $objFormParam->setValue('tel01', null);
        $objFormParam->setValue('tel02', null);
        $objFormParam->setValue('tel03', null);
        $objFormParam->setValue('sex', 0);
        $objFormParam->setValue('password', 'password123');
        $objFormParam->setValue('password02', 'password123');
        $objFormParam->setValue('reminder', 1);
        $objFormParam->setValue('reminder_answer', null);
    }
}
