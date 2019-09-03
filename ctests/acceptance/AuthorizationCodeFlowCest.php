<?php


class AuthorizationCodeFlowCest
{
    public function _before(AcceptanceTester $I, \Codeception\Scenario $scenario)
    {
    }

    public function _after(AcceptanceTester $I, \Codeception\Module\AcceptanceHelper $helper)
    {
        $helper->deleteCustomer('email@example.com');
    }

    // tests
    public function testGetClient(AcceptanceTester $I)
    {
        $I->amOnPage('/sso/XXXX/redirect');
        $I->expect('クライアント取得エラー');
        $I->SeeElement('.error');
    }

    public function testAuthorizaitonCodeNotFound(AcceptanceTester $I)
    {
        $I->wantTo('code が見つからない');
        $I->amOnPage('/sso/DUMMY/redirect');
        $I->see('Authorization code が見つかりませんでした');
    }

    public function testAuthorizaitonCodeFlowError(AcceptanceTester $I)
    {
        $I->wantTo('エラー表示');
        $I->amOnPage('/sso/DUMMY/redirect?error=invalid_request_uri&error_description='.rawurlencode('The request_uri in the Authorization Request returns an error or contains invalid data.'));
        $I->see('invalid_request_uri: The request_uri in the Authorization Request returns an error or contains invalid data.');
    }

    public function testSuccessAuthorizationCodeResponse(AcceptanceTester $I)
    {
        $I->wantTo('Success Authorization code response');
        $I->amOnPage('/sso/DUMMY/redirect?code=authorization_code');

        $I->expect('利用規約を確認し会員登録する');
        $I->see('会員登録');
        $I->selectOption('input[name=mailmaga_flg]', 1);
        $I->click(['id' => 'register']);

        $I->expect('会員登録完了を確認する');
        $I->see('会員登録(完了ページ)');
        $I->seeNumRecords(1, 'dtb_customer', ['email' => 'email@example.com']);

        $I->expect('ログイン完了を確認する');
        $I->see('お名前 様', ['css' => '.user_name']);
    }
}
