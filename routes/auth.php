<?php

use App\Currency\Currency;
use App\Games\Kernel\ProvablyFair;
use App\Mail\ResetPassword;
use App\PasswordReset;
use App\Utils\APIResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\User;

if(!function_exists('createUser')) {
    function createUser($email, $login, $password, $avatar = null, $additionalData = []) {
        $user = User::create(array_merge([
            'name' => $login,
            'password' => $password == null ? null : Hash::make($password),
            'avatar' => $avatar ?? '/avatar/' . uniqid(),
            'email' => $email,
            'client_seed' => ProvablyFair::generateServerSeed(),
            'access' => 'user',
            'name_history' => [['time' => Carbon::now(), 'name' => $login]],
            'register_ip' => User::getIp(),
            'login_ip' => User::getIp(),
            'register_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
            'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null
        ], $additionalData));

        if (isset($_COOKIE['c'])) {
            $referrer = User::where('name', $_COOKIE['c'])->first();
            if ($referrer != null) {
                $user->update(['referral' => $referrer->_id]);
                $user->balance(Currency::all()[0])->add(floatval(Currency::all()[0]->option('referral_bonus')));
            }
        }

        auth()->login($user, true);
        return $user;
    }
}

if(!function_exists('validateCaptcha')) {
    function validateCaptcha($payload): bool {
        return true;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'secret' => \App\Settings::get('recaptcha_secret_key'),
            'response' => $payload,
            'remoteip' => User::getIp()
        ]);

        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $data['success'];
    }
}

Route::middleware('auth:sanctum')->post('/update', function() {
    return APIResponse::success(array_merge(auth('sanctum')->user()->toArray(), [
        'vipLevel' => auth('sanctum')->user()->vipLevel(),
        'vipBonus' => auth('sanctum')->user()->vipBonus()
    ]));
});

Route::post('resetPassword', function(Request $request) {
    if($request->type) {
        if($request->type === 'validateToken') return PasswordReset::where('user', $request->user)->where('token', $request->token)->first() ? APIResponse::success() : APIResponse::reject(2, 'Invalid token');
        if($request->type === 'reset') {
            $user = User::where('_id', $request->user)->first();
            if(!$user || PasswordReset::where('user', $request->user)->where('token', $request->token)->first() == null) return APIResponse::reject(3, 'Invalid token');

            PasswordReset::where('user', $request->user)->where('token', $request->token)->delete();

            $user->update(['password' => Hash::make($request->password)]);
            return APIResponse::success();
        }

        return APIResponse::reject(1, 'Invalid type');
    }

    $user = User::where('email', $request->login)->orWhere('name', $request->login)->first();
    if(!$user) return APIResponse::success();

    $token = ProvablyFair::generateServerSeed();

    PasswordReset::create([
        'user' => $user->_id,
        'token' => $token
    ]);

    Mail::to($user)->send(new ResetPassword($user->_id, $token));

    return APIResponse::success();
});

Route::post('/login', function(Request $request) {
    $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string', 'min:4'],
        'captcha' => ['required']
    ]);

    if(!validateCaptcha($request->captcha)) return APIResponse::reject(2, 'Invalid captcha');

    $user = User::where('email', $request->login)->orWhere('name', $request->login)->first();
    if(!$user || !Hash::check($request->password, $user->password)) return APIResponse::reject(1, 'Wrong credentials');

    $token = $user->createToken()->plainTextToken;
    auth()->login($user, true);

    $user->update([
        'login_ip' => User::getIp(),
        'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
        'tfa_persistent_key' => null,
        'tfa_onetime_key' => null
    ]);

    return APIResponse::success([
        'user' => $user->toArray(),
        'token' => $token
    ]);
});

Route::post('/register', function(Request $request) {
    $request->validate([
        'email' => ['required', 'unique:users', 'email'],
        'name' => ['required', 'unique:users', 'string', 'max:64', 'regex:/^[a-zA-Z0-9]{4,64}$/u'],
        'password' => ['required', 'string', 'min:5'],
        'captcha' => ['required']
    ]);

    if(!validateCaptcha($request->captcha)) return APIResponse::reject(2, 'Invalid captcha');

    $user = createUser($request->email, $request->name, $request->password);

    return APIResponse::success([
        'user' => $user->toArray(),
        'token' => $user->createToken()->plainTextToken
    ]);
});

$redirect_uri = url('/');
Route::get('/vk', function(Request $request) use ($redirect_uri) {
    $client_id = \App\Settings::get('vk_client_id');
    $client_secret = \App\Settings::get('vk_client_secret');

    if(!is_null($request->code)) {
        $obj = json_decode(curl('https://oauth.vk.com/access_token?client_id=' . $client_id . '&client_secret=' . $client_secret . '&redirect_uri=' . $redirect_uri . '/auth/vk&code=' . $request->code));

        if(isset($obj->access_token)) {
            $info = json_decode(curl('https://api.vk.com/method/users.get?fields=photo_200&access_token=' . $obj->access_token . '&v=5.103'), true);

            if(auth('sanctum')->guest()) {
                $photo = array_key_exists('photo_200', $info['response'][0]) ? $info['response'][0]['photo_200'] : null;
                $user = User::where('vk', $info['response'][0]['id'])->first();
                if (is_null($user)) createUser(null, $info['response'][0]['first_name'] . ' ' . $info['response'][0]['last_name'], null, $photo, ['vk' => $info['response'][0]['id']]);
                else {
                    $user->update([
                        'login_ip' => User::getIp(),
                        'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
                        'tfa_persistent_key' => null,
                        'tfa_onetime_key' => null
                    ]);
                    auth('sanctum')->login($user, true);
                }
                return redirect('/');
            } else {
                $id = $info['response'][0]['id'];
                if(User::where('vk', $id)->first() != null) return __('general.profile.somebody_already_linked');
                auth('sanctum')->user()->update(['vk' => $id]);
                return redirect('/user/'.auth('sanctum')->user()->_id.'#settings');
            }
        }
        return redirect('/');
    } else return response()->redirectTo('https://oauth.vk.com/authorize?client_id=' . $client_id . '&display=page&redirect_uri=' . $redirect_uri . '/auth/vk&scope=photos&response_type=code&v=5.53');
});

Route::middleware('auth:sanctum')->post('discord_bonus', function() {
    $r = apiRequest('https://discord.com/api/guilds/'.\App\Settings::get('discord_server_id').'/members/'.auth('sanctum')->user()->discord,
        \App\Settings::get('discord_bot_token'), 'Bot');

    if(($r->message ?? null) != null) return APIResponse::reject(1);
    if(auth('sanctum')->user()->discord_bonus) return APIResponse::reject(2);
    auth('sanctum')->user()->update([
        'discord_bonus' => true
    ]);
    auth('sanctum')->user()->balance(auth('sanctum')->user()->clientCurrency())->add(floatval(auth('sanctum')->user()->clientCurrency()->option('discord')));
    return APIResponse::success();
});

Route::middleware('auth:sanctum')->post('/discord_role', function() {
    if(auth('sanctum')->user()->vipLevel() < 1) return APIResponse::reject(1, 'Hacking attempt');
    $r = apiRequest('https://discord.com/api/guilds/'.\App\Settings::get('discord_server_id').'/members/'.auth('sanctum')->user()->discord.'/roles/'.\App\Settings::get('discord_vip_role_id'),
        \App\Settings::get('discord_bot_token'), 'Bot', 'PUT');
    return APIResponse::success((array) $r);
});

Route::get('/discord', function(Request $request) use($redirect_uri) {
    $client_id = \App\Settings::get('name', 'discord_client_id');
    $client_secret = \App\Settings::get('discord_client_secret');

    if(!is_null($request->code)) {
         $url = 'https://discord.com/api/v6/oauth2/token';
         $params = [
             'client_id' => $client_id,
             'client_secret' => $client_secret,
             'grant_type' => 'authorization_code',
             'code' => $request->code,
             'scope' => 'identify',
             'redirect_uri' => "$redirect_uri/auth/discord"
         ];

         $obj = json_decode(curl($url, $params));
         if(isset($obj->access_token)) {
             $info = apiRequest('https://discord.com/api/users/@me', $obj->access_token);

             if(auth('sanctum')->guest()) {
                 $user = User::where('discord', $info->id)->first();
                 if (is_null($user)) createUser(null, $info->username, null, null, ['discord' => $info->id]);
                 else {
                     $user->update([
                         'login_ip' => User::getIp(),
                         'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
                         'tfa_persistent_key' => null,
                         'tfa_onetime_key' => null
                     ]);
                     auth('sanctum')->login($user, true);
                 }
                 return redirect('/');
             } else {
                 if(User::where('discord', $info->id)->first() != null) return __('general.profile.somebody_already_linked');
                 auth('sanctum')->user()->update([
                     'discord' => $info->id
                 ]);
                 return redirect('/user/'.auth('sanctum')->user()->_id.'#settings');
             }
         } else return json_encode(['error' => 'access_token is not granted']);
    } else return response()->redirectTo("https://discord.com/api/oauth2/authorize?client_id=$client_id&redirect_uri=$redirect_uri/auth/discord&response_type=code&scope=identify");
});

Route::get('/steam', function(Request $request) use($redirect_uri) {
    $openid = new LightOpenID(url('/'));
    if(!$openid->mode) {
        $openid->identity = 'http://steamcommunity.com/openid';
        return response()->redirectTo($openid->authUrl());
    }
    else if($openid->mode == 'cancel') response()->redirectTo('/');
    else {
        if($openid->validate()) {
            $id = $openid->identity;

            $key = \App\Settings::get('steam_web_api_key');
            $response = json_decode(file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.$key.'&steamids='.substr($id, strrpos($id, '/') + 1, strlen($id))), true)['response'];

            if(auth('sanctum')->guest()) {
                $user = User::where('steam', $response['players'][0]['steamid'])->first();
                if (is_null($user)) createUser(null, $response['players'][0]['personaname'], null, null, ['steam' => $response['players'][0]['steamid']]);
                else {
                    $user->update([
                        'login_ip' => User::getIp(),
                        'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
                        'tfa_persistent_key' => null,
                        'tfa_onetime_key' => null
                    ]);
                    auth('sanctum')->login($user, true);
                }
                return redirect('/');
            } else {
                if(User::where('steam', $response['players'][0]['steamid'])->first() != null) return __('general.profile.somebody_already_linked');
                auth('sanctum')->user()->update([
                    'steam' => $response['players'][0]['steamid']
                ]);
                return redirect('/user/'.auth('sanctum')->user()->_id.'#settings');
            }
        }
    }
});

Route::get('/fb', function(Request $request) use($redirect_uri) {
    $client_id = \App\Settings::get('fb_client_id');
    $client_secret = \App\Settings::get('fb_client_secret');

    if(!is_null($request->code)) {
        $url = 'https://graph.facebook.com/v3.2/oauth/access_token';
        $params = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri.'/auth/fb',
            'code' => $request->code,
            'scope' => 'email'
        ];

        $obj = json_decode(file_get_contents($url.'?'.urldecode(http_build_query($params))));
        if (isset($obj->access_token)) {
            $userInfo = json_decode(file_get_contents('https://graph.facebook.com/v3.2/me?fields=id,name,email&access_token='.$obj->access_token), true);

            if(isset($userInfo['id'])) {
                $user_id = $userInfo['id'];

                if(auth('sanctum')->guest()) {
                    $user = User::where('fb', $user_id)->first();
                    if (is_null($user)) createUser(null, $userInfo['name'], null, $userInfo['picture'] ?? null, ['fb' => $user_id]);
                    else {
                        $user->update([
                            'login_ip' => User::getIp(),
                            'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
                            'tfa_persistent_key' => null,
                            'tfa_onetime_key' => null
                        ]);
                        auth('sanctum')->login($user, true);
                    }
                    return redirect('/');
                } else {
                    if(User::where('fb', $user_id)->first() != null) return __('general.profile.somebody_already_linked');
                    auth('sanctum')->user()->update([
                        'fb' => $user_id
                    ]);
                    return redirect('/user/'.auth('sanctum')->user()->_id.'#settings');
                }
            } else return json_encode(['error' => 'user id is not granted']);
        } else return json_encode(['error' => 'access_token is not granted']);
    } else return response()->redirectTo('https://www.facebook.com/v3.2/dialog/oauth?'.urldecode(http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri . '/auth/fb',
        'response_type' => 'code',
        'state' => '{st=xbnf52l,ds=731562}',
        'scope' => 'email'
    ])));
});

Route::get('/google', function(Request $request) use($redirect_uri) {
    $client_id = \App\Settings::get('google_client_id');
    $client_secret = \App\Settings::get('google_client_secret');

    if(!is_null($request->code)) {
        $url = 'https://accounts.google.com/o/oauth2/token';
        $params = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri . '/auth/google',
            'grant_type' => 'authorization_code',
            'code' => $request->code
        );

        $obj = json_decode(curl($url, $params));
        if (isset($obj->access_token)) {
            $params['access_token'] = $obj->access_token;
            $userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query($params))), true);

            if(isset($userInfo['id'])) {
                $user_id = $userInfo['id'];

                if(auth('sanctum')->guest()) {
                    $user = User::where('google', $user_id)->first();
                    if (is_null($user)) createUser(null, $userInfo['name'], null, $userInfo['avatar'] ?? null, ['google' => $user_id]);
                    else {
                        $user->update([
                            'login_ip' => User::getIp(),
                            'login_multiaccount_hash' => request()->hasCookie('s') ? request()->cookie('s') : null,
                            'tfa_persistent_key' => null,
                            'tfa_onetime_key' => null
                        ]);
                        auth('sanctum')->login($user, true);
                    }
                } else {
                    if(User::where('google', $user_id)->first() != null) return __('general.profile.somebody_already_linked');
                    auth('sanctum')->user()->update([
                        'google' => $user_id
                    ]);
                    return redirect('/user/'.auth('sanctum')->user()->_id.'#settings');
                }
                return redirect('/');
            } else return json_encode(['error' => 'user id is not granted']);
        } else return json_encode(['error' => 'access_token is not granted']);
    } else return response()->redirectTo('https://accounts.google.com/o/oauth2/auth?'.urldecode(http_build_query([
            'redirect_uri' => $redirect_uri . '/auth/google',
            'response_type' => 'code',
            'client_id' => $client_id,
            'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
        ])));
});

Route::get('/logout', function() {
    auth('sanctum')->logout();
    return APIResponse::success();
});

if(!function_exists('curl')) {
    function curl($url, $params = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($params != null) curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}

if(!function_exists('apiRequest')) {
    function apiRequest($url, $access_token, $auth = 'Bearer', $post = false, $headers = []){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($post === 'PUT') curl_setopt($ch, CURLOPT_PUT, true);
        else if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

        $headers[] = 'Accept: application/json';

        $headers[] = 'Authorization: ' . $auth . ' ' . $access_token;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        return json_decode($response);
    }
}
