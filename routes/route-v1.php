<?php

$route->group(['prefix' => 'door'], function () use ($route)
{
    $route->post('/detect', 'DoorController@detectAccess');

    $route->post('/message', 'DoorController@sendMessage');

    $route->post('/register', 'DoorController@register');

    $route->post('/login', 'DoorController@login');

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->post('/get_user_info', 'DoorController@getUserInfo');

        $route->post('/logout', 'DoorController@logout');

        $route->post('/oauth_channel', 'DoorController@OauthChannelVerify');

        $route->post('/bind_phone', 'DoorController@bindPhone');

        $route->post('/bind_weapp_user', 'DoorController@bindWechatUser');

        $route->post('/bind_qq_user', 'DoorController@bindQQUser');
    });

    $route->post('/get_wechat_phone', 'DoorController@getWechatPhone');

    $route->post('/wechat_mini_app_login', 'DoorController@wechatMiniAppLogin');

    $route->post('/wechat_mini_app_get_token', 'DoorController@wechatMiniAppToken');

    $route->post('/weapp_mini_app_login', 'DoorController@wechatMiniAppLogin');

    $route->post('/weapp_mini_app_get_token', 'DoorController@wechatMiniAppToken');

    $route->post('/qq_mini_app_login', 'DoorController@qqMiniAppLogin');

    $route->post('/qq_mini_app_get_token', 'DoorController@qqMiniAppToken');

    $route->post('/reset_password', 'DoorController@resetPassword');

    $route->group(['prefix' => '/oauth2'], function () use ($route)
    {
        $route->get('/ticket', 'DoorController@shareTicket');

        $route->post('/qq', 'DoorController@qqAuthRedirect');

        $route->post('/wechat', 'DoorController@wechatAuthRedirect');
    });
});

$route->group(['prefix' => 'user'], function () use ($route)
{
    $route->get('show', 'UserController@show');

    $route->get('relation', 'UserController@getUserRelations');

    $route->get('timeline', 'UserController@timeline');

    $route->get('managers', 'UserController@managers');

    $route->get('idols', 'UserController@idols');

    $route->get('pins', 'UserController@publishedPin');

    $route->get('batch_patch', 'UserController@batchPatch');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('like_bangumi', 'UserController@likeBangumi');

        $route->get('detect_relation', 'UserController@detectUserRelation');

        $route->get('patch', 'UserController@patch');
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->post('update_info', 'UserController@updateProfile');

        $route->post('daily_sign', 'UserController@dailySign');

        $route->get('roles', 'UserController@getRoles');

        $route->post('add_manager', 'UserController@addManager');

        $route->post('remove_manager', 'UserController@removeManager');
    });
});

$route->group(['prefix' => 'search'], function () use ($route)
{
    $route->get('mixin', 'SearchController@mixin');

    $route->get('tags', 'TagController@search');
});

$route->group(['prefix' => 'bangumi'], function () use ($route)
{
    $route->get('timeline', 'BangumiController@timeline');

    $route->get('all', 'BangumiController@all');

    $route->get('show', 'BangumiController@show');

    $route->get('rank', 'BangumiController@rank250');

    $route->get('hot', 'BangumiController@hot100');

    $route->get('release', 'BangumiController@release');

    $route->get('idols', 'BangumiController@idols');

    $route->get('liker', 'BangumiController@liker');

    $route->get('relation', 'BangumiController@relation');

    $route->get('pins', 'BangumiController@pins');

    $route->get('recommended_pins', 'BangumiController@recommendedPins');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('patch', 'BangumiController@patch');

        $route->get('fetch', 'BangumiController@fetch');
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->post('create', 'BangumiController@create');
    });

    $route->group(['prefix' => 'update', 'middleware' => 'user'], function () use ($route)
    {
        $route->post('profile', 'BangumiController@updateProfile');

        $route->post('set_parent', 'BangumiController@updateAsParent');

        $route->post('set_child', 'BangumiController@updateAsChild');

        $route->post('fetch_idols', 'BangumiController@fetchIdols');
    });
});

$route->group(['prefix' => 'join', 'middleware' => 'auth'], function () use ($route)
{
    $route->get('result', 'JoinController@result');

    $route->get('flow', 'JoinController@flow');

    $route->get('list', 'JoinController@list');

    $route->post('create', 'JoinController@create');

    $route->post('recommend', 'JoinController@recommend');

    $route->post('delete', 'JoinController@delete');

    $route->post('begin', 'JoinController@begin');

    $route->post('submit', 'JoinController@submit');

    $route->post('pass', 'JoinController@pass');

    $route->post('vote', 'JoinController@vote');

    $route->group(['prefix' => 'rule'], function () use ($route)
    {
        $route->get('show', 'JoinController@getJoinRule');

        $route->post('update', 'JoinController@updateJoinRule');
    });
});

$route->group(['prefix' => 'idol'], function () use ($route)
{
    $route->get('list', 'IdolController@list');

    $route->get('show', 'IdolController@show');

    $route->get('fans', 'IdolController@fans');

    $route->get('trend', 'IdolController@trend');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('patch', 'IdolController@patch');

        $route->post('vote', 'IdolController@vote');

        $route->post('update', 'IdolController@update');

        $route->get('fetch', 'IdolController@fetch');
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->post('create', 'IdolController@create');
    });

    $route->get('batch_patch', 'IdolController@batchPatch');
});

$route->group(['prefix' => 'message'], function () use ($route)
{
    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->get('total', 'MessageController@getMessageTotal');

        $route->post('send', 'MessageController@sendMessage');

        $route->get('menu', 'MessageController@getMessageMenu');

        $route->get('history', 'MessageController@getChatHistory');

        $route->post('unread_clear', 'MessageController@clearUnread');

        $route->get('message_pin_comment', 'MessageController@messageOfComment');

        $route->get('message_agree', 'MessageController@messageOfAgree');

        $route->get('message_pin_reward', 'MessageController@messageOfReward');

        $route->get('message_user_follow', 'MessageController@messageOfFollow');

        $route->get('get_channel', 'MessageController@getMessageChannel');

        $route->post('delete_channel', 'MessageController@deleteMessageChannel');

        $route->post('clear_channel', 'MessageController@clearMessageChannel');
    });
});

$route->group(['prefix' => 'image'], function () use ($route)
{
    $route->get('captcha', 'ImageController@captcha');

    $route->get('uptoken', 'ImageController@uptoken');
});

$route->group(['prefix' => 'social', 'middleware' => 'auth'], function () use ($route)
{
    $route->post('toggle', 'ToggleController@toggle');

    $route->post('vote', 'ToggleController@vote');
});

$route->group(['prefix' => 'pin'], function () use ($route)
{
    $route->get('show', 'PinController@show');

    $route->get('marked_tag', 'PinController@getMarkedTag');

    $route->get('vote_stat', 'PinController@voteStat');

    $route->get('timeline', 'PinController@timeline');

    $route->get('batch_patch', 'PinController@batchPatch');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('patch', 'PinController@patch');
    });

    $route->group(['middleware' => ['auth']], function () use ($route)
    {
        $route->post('create/story', 'PinController@createStory');

        $route->get('update/content', 'PinController@getEditableContent');

        $route->post('update/story', 'PinController@updateStory');

        $route->post('delete', 'PinController@deletePin');

        $route->post('pass', 'PinController@passPin');

        $route->post('move', 'PinController@movePin');

        $route->post('recommend', 'PinController@recommendPin');

        $route->get('drafts', 'PinController@userDrafts');
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->get('trials', 'PinController@trials');

        $route->post('resolve', 'PinController@resolve');

        $route->post('reject', 'PinController@reject');
    });

    $route->group(['prefix' => 'editor'], function () use ($route)
    {
        $route->get('fetch_site', 'PinController@fetchSiteMeta');
    });
});

$route->group(['prefix' => 'tag'], function () use ($route)
{
    $route->get('show', 'TagController@show');

    $route->get('hottest', 'TagController@hottest');

    $route->get('children', 'TagController@children');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('patch', 'TagController@patch');
    });

    $route->get('batch_patch', 'TagController@batchPatch');

    $route->get('bookmarks', 'TagController@bookmarks');

    $route->group(['middleware' => ['auth']], function () use ($route)
    {
        $route->post('create', 'TagController@create');

        $route->post('update', 'TagController@update');

        $route->post('delete', 'TagController@delete');

        $route->post('combine', 'TagController@combine');

        $route->post('relink', 'TagController@relink');
    });
});

$route->group(['prefix' => 'comment'], function () use ($route)
{
    $route->get('show', 'CommentController@show');

    $route->get('list', 'CommentController@list');

    $route->get('talk', 'CommentController@talk');

    $route->group(['middleware' => 'user'], function () use ($route)
    {
        $route->get('patch', 'CommentController@patch');
    });

    $route->group(['middleware' => ['auth']], function () use ($route)
    {
        $route->post('create', 'CommentController@create');

        $route->post('delete', 'CommentController@delete');
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->get('trials', 'PinController@trials');

        $route->post('resolve', 'PinController@resolve');

        $route->post('reject', 'PinController@reject');
    });
});

$route->group(['prefix' => 'cm'], function () use ($route)
{
    $route->get('index_banner', 'CmController@showBanners');

    $route->post('report_banner', 'CmController@reportBannerStat');

    $route->get('index_menu_list', 'CmController@getMenuList');

    $route->get('index_menu_stat', 'CmController@getMenuStat');

    $route->post('report_menu_click', 'CmController@reportMenuStat');
});

$route->group(['prefix' => 'flow'], function () use ($route)
{
    $route->get('spider', 'FlowController@spiderFlow');

    $route->get('spider_hots', 'FlowController@spiderHots');

    $route->post('spider_report', 'FlowController@spiderReport');

    $route->group(['prefix' => 'pin'], function () use ($route)
    {
        $route->get('newest', 'FlowController@pinNewest');

        $route->get('hottest', 'FlowController@pinHottest');

        $route->get('activity', 'FlowController@pinActivity');

        $route->get('trial', 'FlowController@pinTrial');
    });

    $route->group(['prefix' => 'idol'], function () use ($route)
    {
        $route->get('newest', 'FlowController@idolNewest');

        $route->get('hottest', 'FlowController@idolHottest');

        $route->get('activity', 'FlowController@idolActivity');
    });
});

$route->group(['prefix' => 'live_room'], function () use ($route)
{
    $route->get('show', 'LiveRoomController@show');

    $route->group(['prefix' => 'list'], function () use ($route)
    {
        $route->get('activity', 'LiveRoomController@activeLiveChat');
    });

    $route->group(['prefix' => 'voice'], function () use ($route)
    {
        $route->get('all', 'LiveRoomController@allVoice');

        $route->group(['middleware' => 'auth'], function () use ($route)
        {
            $route->post('create', 'LiveRoomController@createUserVoice');

            $route->post('update', 'LiveRoomController@updateUserVoice');

            $route->post('delete', 'LiveRoomController@deleteUserVoice');
        });
    });

    $route->group(['middleware' => 'auth'], function () use ($route)
    {
        $route->get('drafts', 'LiveRoomController@drafts');

        $route->post('delete', 'LiveRoomController@delete');

        $route->post('publish', 'LiveRoomController@publishLive');
    });
});

$route->group(['prefix' => 'console', 'middleware' => 'auth'], function () use ($route)
{
    $route->group(['prefix' => 'bangumi'], function () use ($route)
    {
        $route->get('list', 'BangumiController@bangumiList');

        $route->get('serializations', 'BangumiController@allSerialization');

        $route->post('set_serialization', 'BangumiController@setBangumiSerializing');
    });

    $route->group(['prefix' => 'live_room'], function () use ($route)
    {
        $route->group(['prefix' => 'voice'], function () use ($route)
        {
            $route->get('list', 'LiveRoomController@idolVoiceList');

            $route->post('create', 'LiveRoomController@createIdolVoice');

            $route->post('update', 'LiveRoomController@updateIdolVoice');

            $route->post('delete', 'LiveRoomController@deleteIdolVoice');
        });
    });

    $route->group(['prefix' => 'spider'], function () use ($route)
    {
        $route->get('get_all_user', 'SpiderController@getUsers');

        $route->get('get_user_rule', 'SpiderController@getUserRule');

        $route->post('save_user_rule', 'SpiderController@saveUserRule');

        $route->post('set_user', 'SpiderController@setUser');

        $route->post('del_user', 'SpiderController@delUser');

        $route->post('refresh_user_data', 'SpiderController@refreshUserData');

        $route->group(['prefix' => 'oauth'], function () use ($route)
        {
            $route->get('get_channel_list', 'SpiderController@getChannelList');

            $route->post('set_channel_cookie', 'SpiderController@setChannelCookie');
        });
    });

    $route->group(['prefix' => 'cm'], function () use ($route)
    {
        $route->get('show_all_banner', 'CmController@allBanners');

        $route->post('create_banner', 'CmController@createBanner');

        $route->post('update_banner', 'CmController@updateBanner');

        $route->post('toggle_banner', 'CmController@toggleBanner');

        $route->get('show_all_menu_list', 'CmController@getAllMenuList');

        $route->get('show_all_menu_type', 'CmController@getAllMenuType');

        $route->post('create_menu_type', 'CmController@createMenuType');

        $route->post('create_menu_link', 'CmController@createMenuLink');

        $route->post('delete_menu_link', 'CmController@deleteMenuLink');
    });

    $route->group(['prefix' => 'role'], function () use ($route)
    {
        $route->get('show_all_roles', 'RoleController@showAllRoles');

        $route->get('show_all_users', 'RoleController@getUsersByCondition');

        $route->post('create_role', 'RoleController@createRole');

        $route->post('create_permission', 'RoleController@createPermission');

        $route->post('toggle_permission_to_role', 'RoleController@togglePermissionToRole');

        $route->post('toggle_role_to_user', 'RoleController@toggleRoleToUser');
    });

    $route->group(['prefix' => 'trial'], function () use ($route)
    {
        $route->group(['prefix' => 'words'], function () use ($route)
        {
            $route->get('show', 'TrialController@showWords');

            $route->get('blocked', 'TrialController@getBlockedWords');

            $route->post('test', 'TrialController@textTest');

            $route->post('add', 'TrialController@addWords');

            $route->post('delete', 'TrialController@deleteWords');

            $route->post('clear', 'TrialController@clearBlockedWords');
        });

        $route->post('image/test', 'TrialController@imageTest');

        $route->get('stat', 'TrialController@trialStat');
    });
});
