<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_header.tpl" subtitle="パスワードを忘れた方(入力ページ)"}-->
<div id="undercolumn">
    <div id="undercolumn_entry">
        <h2 class="title"><!--{$tpl_title|h}--></h2>
        <p>下記の内容で送信してもよろしいでしょうか？<br />
            よろしければ、一番下の「会員登録をする」ボタンをクリックしてください。</p>
        <form name="form1" id="form1" method="post" action="?">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="complete">
            <!--{foreach from=$arrForm key=key item=item}-->
            <input type="hidden" name="<!--{$key|h}-->" value="<!--{$item.value|h}-->" />
            <!--{/foreach}-->

            <table summary="入力内容確認">
                <col width="30%" />
                <col width="70%" />
                <tr>
                    <th>お名前</th>
                    <td>
                        <!--{assign var=key1 value="`$prefix`name01"}-->
                        <!--{$arrForm[$key1].value|h}-->
                    </td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                    <td>
                        <!--{assign var=key1 value="`$prefix`email"}-->
                        <a href="mailto:<!--{$arrForm[$key1].value|escape:'hex'}-->"><!--{$arrForm[$key1].value|escape:'hexentity'}--></a>
                    </td>
                </tr>
                <tr>
                    <th>メールマガジン送付について</th>
                    <td>
                        <!--{assign var=key1 value="`$prefix`mailmaga_flg"}-->
                        <!--{assign var="mailmaga_flg_id" value=$arrForm[$key1].value}-->
                        <!--{$arrMAILMAGATYPE[$mailmaga_flg_id]|h}-->
                    </td>
                </tr>
            </table>

            <div class="btn_area">
                <ul>
                    <li>
                        <a href="?" onclick="eccube.setModeAndSubmit('return', '', ''); return false;">
                            <img class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_back.jpg" alt="戻る" />
                        </a>
                    </li>
                    <li>
                        <input type="image" class="hover_change_image" src="<!--{$TPL_URLPATH}-->img/button/btn_entry.jpg" alt="会員登録をする" name="send" id="send" />
                    </li>
                </ul>
            </div>

        </form>
    </div>
</div>
<!--{include file="`$smarty.const.TEMPLATE_REALDIR`popup_footer.tpl"}-->
