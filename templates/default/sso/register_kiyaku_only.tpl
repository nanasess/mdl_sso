<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="パスワードを忘れた方(入力ページ)"}-->
<div id="mypagecolumn">
    <h2 class="title"><!--{$tpl_title|h}--></h2>

    <div id="mycontents_area">
        <h3><!--{$tpl_subtitle|h}--></h3>
        <p>下記項目にご入力ください。「<span class="attention">※</span>」印は入力必須項目です。<br />
            入力後、一番下の「確認ページへ」ボタンをクリックしてください。</p>

        <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="complete" />
            <input type="hidden" name="customer_id" value="<!--{$arrForm.customer_id.value|h}-->" />

            <p class="message">【重要】 会員登録をされる前に、下記ご利用規約をよくお読みください。</p>
            <p>規約には、本サービスを使用するに当たってのあなたの権利と義務が規定されております。<br />
                「同意して会員登録へ」ボタンをクリックすると、あなたが本規約の全ての条件に同意したことになります。
            </p>

            <textarea name="textfield" class="kiyaku_text" cols="80" rows="10" readonly="readonly"><!--{"\n"}--><!--{$tpl_kiyaku_text|h}--></textarea>
            <table summary="会員登録 " class="delivname">
                <tr>
                    <th>メールマガジン送付について<span class="attention">※</span></th>
                    <td>
                        <!--{assign var=key1 value="`$prefix`mailmaga_flg"}-->
                        <!--{if $arrErr[$key1]}-->
                        <div class="attention"><!--{$arrErr[$key1]}--></div>
                        <!--{/if}-->
                        <span style="<!--{$arrErr[$key1]|sfGetErrorColor}-->">
                            <!--{html_radios name=$key1 options=$arrMAILMAGATYPE selected=$arrForm[$key1].value separator='<br />'}-->
                        </span>
                    </td>
                </tr>
            </table>
                <div class="btn_area">
                    <ul>
                        <li>
                            <a href="<!--{$smarty.const.TOP_URL}-->" id="cancel">
                                <img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_entry_cannot.jpg" alt="同意しない" />
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;" onclick="document.form1.submit(); return false;" id="register">
                                <img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_entry_agree.jpg" alt="同意して会員登録へ" />
                            </a>
                        </li>
                    </ul>
                </div>
        </form>
    </div>
</div>
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_footer.tpl"}-->
