<?php


class AuthorizationCodeFlowCest
{
    public function _before(AcceptanceTester $I, \Codeception\Scenario $scenario)
    {
    }

    public function _after(AcceptanceTester $I)
    {
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
        $I->DontSeeElement('.error');

        $I->expect('会員登録をします');
        $name = $I->grabValueFrom('name01');
        $I->assertNotNull($name);
        $email = $I->grabValueFrom('email');
        $I->assertNotNull($email);
        $I->selectOption('input[name=mailmaga_flg]', 'HTMLメール');
        $I->click(['xpath' => '//*[@id="form1"]/div/ul/li[2]/a']); // 確認画面へ

        $I->expect('会員登録内容を確認します');
        $I->see('下記の内容で送信してもよろしいでしょうか？');
        $I->click('#send');

        $I->expect('登録完了');
        $I->see('本登録が完了いたしました。');
        $I->click('//*[@id="complete_area"]/div[2]/ul/li/a'); // TOPページへ

        $I->seeInDatabase('dtb_customer', ['email' => $email]);
    }
}
