<?php


class AuthorizationCodeFlowCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->getScenario()->incomplete('Not implemented');
    }

    public function _after(AcceptanceTester $I)
    {
    }

    // tests
    public function testGetClient(AcceptanceTester $I)
    {
        $I->amOnPage('/test/sso/mockcallback.php?short_name=XXXX');
        $I->expect('クライアント取得エラー');
        $I->SeeElement('.error');
    }

    public function testAuthorizaitonCodeNotFound(AcceptanceTester $I)
    {
        $I->wantTo('code が見つからない');
        $I->amOnPage('/test/sso/mockcallback.php?short_name=DUMMY');
        $I->see('Authorization code が見つかりませんでした');
    }

    public function testAuthorizaitonCodeFlowError(AcceptanceTester $I)
    {
        $I->wantTo('エラー表示');
        $I->amOnPage('/test/sso/mockcallback.php?short_name=DUMMY&error=invalid_request_uri&error_description='.rawurlencode('The request_uri in the Authorization Request returns an error or contains invalid data.'));
        $I->see('invalid_request_uri: The request_uri in the Authorization Request returns an error or contains invalid data.');
    }

    public function testSuccessAuthorizationCodeResponse(AcceptanceTester $I)
    {
        $I->wantTo('Success Authorization code response');
        $I->amOnPage('/test/sso/mockcallback.php?short_name=DUMMY&code=authorization_code');
        $I->DontSeeElement('.error');
    }
}
