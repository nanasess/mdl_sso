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
        $I->resetEmails();
        $faker = Codeception\Util\Fixtures::get('faker');
        $code = $faker->uuid;
        $I->amOnPage('/sso/DUMMY/redirect?code='.$code);
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
        $I->see('ようこそ '.$name);
        $customer_id = $I->grabFromDatabase('dtb_customer', 'customer_id', ['email' => $email]);
        $I->seeInDatabase('dtb_oauth2_openid_userinfo', ['customer_id' => $customer_id, 'sub' => $code]);
        $I->seeInDatabase('dtb_oauth2_openid_userinfo_address', ['customer_id' => $customer_id]);
        $access_token = $I->grabFromDatabase('dtb_oauth2_token', 'access_token', ['customer_id' => $customer_id]);
        $I->assertNotNull($access_token);
        $refresh_token = $I->grabFromDatabase('dtb_oauth2_token', 'refresh_token', ['customer_id' => $customer_id]);
        $I->assertNotNull($refresh_token);
        $expires_in = $I->grabFromDatabase('dtb_oauth2_token', 'expires_in', ['customer_id' => $customer_id]);
        $I->assertGreaterOrEquals(3600, $expires_in);

        $I->expect('会員登録完了メールを確認する');
        $I->seeEmailCount(1);
        $I->seeInLastEmailSubjectTo($email, '会員登録のご完了');

        $I->expect('強制的に有効期限を終了します');
        $expire_date = date('Y-m-d H:i:s', strtotime('-3 days'));
        $I->updateInDatabase(
            'dtb_oauth2_token',
            [
                'create_date' => $expire_date,
                'update_date' => $expire_date
            ], ['customer_id' => $customer_id]);

        $I->expect('refresh token を使用してアクセストークンを取得する');
        $I->amOnPage('/mypage');
        $I->see('ようこそ '.$name);
        $update_date = $I->grabFromDatabase('dtb_oauth2_token', 'update_date', ['customer_id' => $customer_id]);
        $I->assertEquals(date('Y-m-d'), date('Y-m-d', strtotime($update_date)));

        $I->expect('有効期限切れを確認する');
        $I->updateInDatabase(
            'dtb_oauth2_token',
            [
                'refresh_token' => 'not_refresh',
                'create_date' => $expire_date,
                'update_date' => $expire_date
            ], ['customer_id' => $customer_id]);
        $I->amOnPage('/mypage');
        $I->see('MYページ(ログイン)');

        $I->expect('再ログインする');
        $I->amOnPage('/sso/DUMMY/redirect?code='.$code);
        $I->see('ようこそ '.$name);

        $I->expect('ログアウトする');
        $I->click(['xpath' => '//*[@id="login_form"]/div/p[2]/input']); // ログアウト
        $I->amOnPage('/mypage');
        $I->see('MYページ(ログイン)');
    }
}
