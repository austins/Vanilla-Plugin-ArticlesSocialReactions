<?php defined('APPLICATION') or exit();
/**
 * Copyright (C) 2015  Austin S. (Shadowdare)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$PluginInfo['ArticlesSocialReactions'] = array(
    'Name' => 'Articles - Social Reactions',
    'Description' => 'Adds social media features to the Articles application.',
    'Version' => '1.0.0',
    'RequiredApplications' => array('Articles' => '1.0.0'),
    'MobileFriendly' => true,
    'Author' => 'Austin S. (Shadowdare)',
    'AuthorUrl' => 'http://vanillaforums.org/profile/addons/16014/Shadowdare',
    'License' => 'GNU GPL2',
    'SettingsUrl' => '/dashboard/settings/articlessocialreactions'
);

class ArticlesSocialReactionsPlugin extends Gdn_Plugin {
    /**
     * Add links for the setting pages to the dashboard sidebar.
     *
     * @param Gdn_Controller $Sender
     */
    public function Base_GetAppSettingsMenuItems_Handler($Sender) {
        $GroupName = 'Articles';

        /* @var SideMenuModule $Menu */
        $Menu = &$Sender->EventArguments['SideMenu'];

        if (isset($Menu->Items[$GroupName]))
            $Menu->AddLink($GroupName, T('Social Reactions'), '/dashboard/settings/articlessocialreactions',
                'Garden.Settings.Manage');
    }

    /**
     * @param SettingsController $Sender
     */
    public function SettingsController_ArticlesSocialReactions_Create($Sender) {
        $Sender->Permission('Garden.Settings.Manage');
        $Sender->Title($this->GetPluginName() . ' ' . T('Settings'));
        $Sender->AddSideMenu('/dashboard/settings/articlessocialreactions');

        $ConfigModule = new ConfigurationModule($Sender);

        $ConfigModule->Initialize(array(
            'Plugins.ArticlesSocialReactions.FacebookAppId' => array(
                'LabelCode' => '[Facebook] Enter a Facebook app ID associated with this website:',
                'Description' => 'Leave blank to not display the Facebook social reaction.',
                'Control' => 'TextBox'
            ),
            'Articles.TwitterUsername' => array(
                'LabelCode' => '[Twitter] Enter a Twitter username associated with this website:',
                'Description' => 'This is the same as the Twitter username setting on the Articles Settings page. Leave blank to not display the Twitter social reaction.',
                'Control' => 'TextBox'
            ),
            'Plugins.ArticlesSocialReactions.TwitterHashtag' => array(
                'LabelCode' => '[Twitter] Enter one word to be used for the hashtag in the Twitter social reaction (optional):',
                'Control' => 'TextBox'
            ),
            'Plugins.ArticlesSocialReactions.ShowGooglePlus' => array(
                'LabelCode' => '[Google+] Show Google Plus social reaction links?',
                'Control' => 'CheckBox'
            )
        ));

        $Sender->ConfigurationModule = $ConfigModule;

        $Sender->ConfigurationModule->RenderAll();
    }

    /**
     * Print social media sharing links for articles.
     *
     * @param ArticleController $Sender
     */
    public function ArticleController_AfterArticle_Handler($Sender) {
        $Sender->AddCssFile('articlessocialreactions.css', $this->GetPluginFolder(false));

        // $article = $Sender->Article;
        $articleUrl = $Sender->CanonicalUrl();

        echo '<div id="articles-social-media-buttons" class="FormWrapper FormWrapper-Condensed BoxAfterArticle">
            <h2 class="H">' . T('Share this Article') . '</h2>';

        // Facebook
        $facebook = '';
        $facebookAppId = C('Plugins.ArticlesSocialReactions.FacebookAppId', false);

        if (is_numeric($facebookAppId)) {
            $facebook = '<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=' . $facebookAppId . '&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, "script", "facebook-jssdk"));</script>

<div class="fb-like" data-href="' . $articleUrl . '" data-layout="standard" data-action="recommend" data-show-faces="true" data-share="true"></div>';

            echo Wrap($facebook, 'div', array('id' => 'articles-social-reactions-facebook', 'class' => 'articles-social-media-button'));
        }

        // Twitter
        $twitter = '';
        $twitterUsername = C('Articles.TwitterUsername', '');
        $twitterHashtag = C('Plugins.ArticlesSocialReactions.TwitterHashtag', '');

        // Make sure Twitter hashtag has no spaces.
        if (preg_match('/\s/', $twitterHashtag) === 1)
            $twitterHashtag = '';

        if (strlen($twitterUsername) > 0) {
            $twitter = '<div id="articles-social-reactions-twitter"><a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $articleUrl . '" data-via="' . $twitterUsername . '" data-size="large" data-related="' . $twitterUsername . '" data-hashtags="' . $twitterHashtag . '">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script></div>';
        }

        // Google+
        $google = '';
        if (C('Articles.TwitterUsername', false)) {
            $google = '<div id="articles-social-reactions-google"><script src="https://apis.google.com/js/platform.js" async defer></script>
  <g:plusone></g:plusone></div>';
        }

        // Display Twitter and Google+ sharing links on same line.
        if (($twitter !== '') || ($google !== '')) {
            echo Wrap($twitter . $google, 'div', array('class' => 'articles-social-media-button'));
        }

        echo '</div>';
    }
}
