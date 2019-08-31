<?php


class EntryCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function _after(AcceptanceTester $I)
    {
    }


    public function testCustomerRegister(AcceptanceTester $I)
    {
        $faker = Codeception\Util\Fixtures::get('faker');
        $new_email = microtime(true).'.'.$faker->safeEmail;

        $I->wantTo('会員登録');
        $I->amOnPage('/entry/kiyaku.php');
        $I->see('ご利用規約');

        // 同意するをクリック
        $I->click(['xpath' => '//*[@id="form1"]/div/ul/li[2]/a']);
        $I->see('会員登録(入力ページ)');

        $form = [
            'name01' => $faker->lastName,
            'name02' => $faker->firstName,
            'kana01' => $faker->lastKanaName,
            'kana02' => $faker->firstKanaName,
            'zip01' => '530',
            'zip02' => '0001',
            'pref' => $faker->numberBetween(1, 47),
            'addr01' => $faker->city,
            'addr02' => $faker->streetAddress,
            'tel01' => '111',
            'tel02' => '111',
            'tel03' => '111',
            'email' => $new_email,
            'email02' => $new_email,
            'password' => 'password999',
            'password02' => 'password999',
            'sex' => (string) $faker->numberBetween(1, 2),
            'reminder' => (string) $faker->numberBetween(1, 7),
            'reminder_answer' => $faker->word,
            'mailmaga_flg' => '1'
        ];
        $I->submitForm('#form1', $form, '#confirm');

        $I->see('会員登録(確認ページ)');

        $I->see($new_email);
        $I->see($form['zip01'].' - '.$form['zip02']);

        $I->click(['id' => 'send']);

        $I->see('本登録が完了いたしました');
    }
}
