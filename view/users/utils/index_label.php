<?php
// get language set from navigator
$langStr = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
//
if (preg_match("/zh-c/i", $langStr)) {
    $lang = 'zh';
} else {
    $lang = 'en';
}

if ($lang == 'zh') {
    $_SESSION['language'] = 'zh';
    $labels = array(
        // index
        'noJs' => '您的浏览器禁止了脚本运行,本系统无法启动!',
        'title1' => '正确的事通常也是',
        'title2' => '简单的！',
        'title3' => '分享目标， 分享成就 ！',
        'home' => '主页',
        'join' => '加入我们',
        'about' => '关于',
        'login' => '登录',
        'register' => '注册',
        'logo' => '找帮手',
        'logout' => '退出',
        'userSet' => '用户设置',
        'shareInfo' => '信息分享',
        'help' => '帮助',
        'feedback' => '反馈',
        'privateRight' => '隐私权和使用条款',
        'alertTimeout' => '服务器响应超时，请重新提交',
        'investors' => '投资者',
        'version' => '测试版',
        // sign up
        'firstName' => '名字',
        'firstNamePrompt' => '名字(小于18个字符)',
        'lastName' => '姓氏',
        'lastNamePrompt' => '姓氏(小于18个字符)',
        'email' => '电子邮箱',
        'emailPrompt' => '邮箱是您找回密码的唯一途径',
        'password' => '密码',
        'passwordPrompt' => '密码(5到22个字符)',
        'confirmPassword' => '确认密码',
        'register' => '注册',
        'captcha' => '验证码',
        'languageSet' => '语言',
        'accept' => '我已阅读并接受',
        'terms' => '用户协议',
        'successRegister' => '你已经注册成功，请登录。',
        'registerError' => '注册失败，请重新注册。',
        
        // login
        'forgetPassword' => '忘记密码',
        'jsNoSuchEmail' => '没有这个注册邮箱',
        'invalidatePassword' => '用户名与密码不符，请重新输入！',
        'no' => '第',
        'loginErrorTimes' => '次登录错误，总共有10次机会',
        'closedAccount' => '该账号被关闭，请联系管理员',
        
        // reset password
        'confirm_closeWindow' => '信息已经成功修改，5秒钟后本网页会跳转到主页',
        'nextStep' => '下一步',
        'resetEmail' => '需要找回密码的邮箱账号',
        'randomPassword' => '请输入邮箱收到的验证码',
        'resetPassword' => '重置密码',
        'confirmAccount' => '确认账号',
        'captchaCode' => '安全验证',
        'emailError' => '您输入的邮箱没有注册',
        'captchaError' => '您输入的密码有误，请重新输入',
        'captcha_Error' => '验证码有误',
        'passwordError' => '您输入的密码有误，请重新输入',
        'emailSuccess' => '临时密码已经发送到注册邮箱',
        'captchaSuccess' => '请输入重置的密码',
        'passwordSuccess' => '重置密码成功，请登录',
        // help
        'helpSupport' => '帮助 & 支持',
        'needHelp' => '需要帮助',
        'helpContent' => '我们尽自己的最大努力让应用简单易用，所以当你将鼠标划过所有的按钮时
            可以看到相应的解释。如果你需要更进一步的解释，请发送邮件给我们，我们非常乐意回答你的问题。',
        'mailHint' => '点击发送你的邮件 :',
        'aboutUs' => '4Helper is a collaboration and communication platform that provides a single
			place for shared targets, discussions. 4Helper is simple to use
			and flexible. Founded in 2014, the company is privately held.',
        'about4Helper' => 'About 4Helper',
        'lookForTalent' => 'Looking for talent',
        'contentOfJoin' => '4Helper is a new company
			based in Beijing China. We love delivering apps that transform the
			way our customers work. We have vision and are passionate - and
			always have time to listen to our customers. We work hard and also
			have fun.'
    );
} else {
    $_SESSION['language'] = 'en';
    $labels = array(
        'noJs' => 'You browser forbid javascrip, the application can not run.',
        'title1' => 'Right always is ',
        'title2' => 'simple!',
        'title3' => 'Share Targets, Share Success!',
        'home' => 'Home',
        'join' => 'Join us',
        'about' => 'About',
        'login' => 'Login',
        'register' => 'Sign Up',
        'logo' => '4Helper',
        'logout' => 'Logout',
        'userSet' => 'User Set',
        'shareInfo' => 'Share information',
        'help' => 'Help',
        'feedback' => 'Contact us',
        'privateRight' => 'Privacy&Terms',
        'alertTimeout' => 'Server Timeout, pleaes resubmit.',
        'investors' => 'Investors',
        'version' => 'Test version',
        // sign up
        'firstName' => 'First Name',
        'firstNamePrompt' => 'First Name',
        'lastName' => 'Last Name',
        'lastNamePrompt' => 'Last Name',
        'email' => 'Email',
        'emailPrompt' => 'This is the only way to reset your password.',
        'password' => 'Password',
        'passwordPrompt' => 'Password',
        'confirmPassword' => 'Confirm Password',
        'register' => 'Sign Up',
        'captcha' => 'Captcha Code',
        'languageSet' => 'Language',
        'accept' => 'I am accepting the ',
        'terms' => 'Terms of Service',
        'successRegister' => 'You have success signed up. Please login in 4helper.',
        'registerError' => 'Failed sign up, please try again',
        // login
        'forgetPassword' => 'Forget your password',
        'jsNoSuchEmail' => 'No such user',
        'invalidatePassword' => 'Sorry, that\'s not the correct password.',
        'no' => 'No',
        'loginErrorTimes' => 'th login error，you have 10 chances',
        'closedAccount' => 'This is a closed account, please contact admin',
        // reset password
        'confirm_closeWindow' => 'Information has been update successfully, this windown will close in 5 senconds',
        'nextStep' => 'Next Step',
        'resetEmail' => 'Email of the reset account',
        'randomPassword' => 'Please enter the password which is sent to your email',
        'resetPassword' => 'Reset Password',
        'confirmAccount' => 'Confirm',
        'captchaCode' => 'Captcha',
        'emailError' => 'This isn\'t a sign up email box',
        'captchaError' => 'Please enter the right password',
        'captcha_Error' => 'Captcha is wrong',
        'passwordError' => 'Please enter the right password',
        'emailSuccess' => 'Password has been sent to your email',
        'captchaSuccess' => 'Please enter reset password',
        'passwordSuccess' => 'Password has been reset, please login',
        // help
        'helpSupport' => 'Help & Support',
        'needHelp' => 'Need Help',
        'helpContent' => 'We try to make the application as simple
			as we can, so you can move your mouse to every buttons and get an
			explaination. If you need further help, write us and we’ll be happy
			to answer your questions.',
        'mailHint' => 'Please email your questions :',
        // info
        'aboutUs' => '4Helper is a collaboration and communication platform that provides a single
			place for shared targets, discussions. 4Helper is simple to use
			and flexible. Founded in 2014, the company is privately held.',
        'about4Helper' => 'About 4Helper',
        'lookForTalent' => 'Looking for talent',
        'contentOfJoin' => '4Helper is a new company
			based in Beijing China. We love delivering apps that transform the
			way our customers work. We have vision and are passionate - and
			always have time to listen to our customers. We work hard and also
			have fun.'
    );
}
