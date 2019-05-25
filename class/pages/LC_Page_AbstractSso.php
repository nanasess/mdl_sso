<?php

abstract class LC_Page_AbstractSso extends LC_Page_Ex
{
    /** @var string */
    protected $short_name;
    /** @var array */
    protected $arrClient;

    /** @var array */
    protected $vars;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->short_name = htmlspecialchars($this->vars['short_name'], ENT_QUOTES);
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    public function action()
    {
        if (!SC_Helper_OAuth2::validateShortName($this->short_name)) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, 'OAuth2.0 クライアントが見つかりません');
            SC_Response_Ex::actionExit();
        }

        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error'], ENT_QUOTES);
            $error_description = htmlspecialchars($_GET['error_description'], ENT_QUOTES);
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, $error.': '.$error_description);
            SC_Response_Ex::actionExit();
        }
    }

    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }
}
