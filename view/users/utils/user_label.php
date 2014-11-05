<?php
$lang = $_SESSION['language'];
if (! $lang) {
    // default language is english
    $lang = 'en';
}
if ($lang == 'zh') {
    $user_labels = array(
        // top line
        'help' => '帮助',
        'logout' => '退出',
        'userSet' => '用户设置',
        'version' => '测试版',
        
        // home
        'targets' => '目标',
        'mylogo' => '找帮手',
        'closedTargets' => '关闭的目标',
        'helpers' => '帮手群',
        'pleaseUpdate' => '请更新图片',
        
        // targets
        'createTargets' => '创建目标',
        'targetsByMe' => '我创建的目标',
        'targetsShared' => '与我分享的目标',
        'hitTarget' => '完成目标',
        'hitTargetTitle' => '确认完成的目标不能再更新',
        'closeTarget' => '关闭目标',
        'closeTargetTitle' => '关闭后的目标可以在关闭的目标中查询',
        'agreeTarget' => '共识目标',
        'agreeTargetTitle' => '只有帮手可以更新共识目标',
        'shareTarget' => '分享目标',
        'shareTargetTitle' => '只有帮手可以更新分享的目标',
        'modifyTarget' => '修改目标',
        'modifyTargetTitle' => '帮手修改了目标',
        'rejectedTarget' => '被拒绝目标',
        'rejectedTargetTitle' => '抱歉，帮手拒绝了目标',
        'createrRejectTitle' => '帮手可以重新提交被拒绝的目标',
        'missTarget' => '错失目标',
        'missTargetTitle' => '确认错失的目标不能再更新',
        'agree' => '同意',
        'reject' => '拒绝',
        'delete' => '删除',
        'modify' => '修改',
        'hit' => '完成',
        'miss' => '错失',
        'addSubTarget' => '增加一个子目标',
        'addSubTargetTitle' => '增加子目标',
        'writeComment' => '写一个留言...',
        'send' => '发送',
        'targetName' => '目标名称',
        'targetEndTime' => '目标完成时间',
        'targetDescript' => '目标描述',
        'shareModule' => '分享目标',
        'asignSigleMember' => '一个帮手',
        'asignMutipleMember' => '多个帮手',
        'selectReceiver' => '选择帮手',
        'submit' => '提交',
        
        // helper
        'myHelpers' => '我的帮手群',
        'helpers' => '帮手',
        'invitations' => '邀请',
        'addHelper' => '添加帮手',
        'email' => '电子邮箱',
        'greeting' => '写句话邀请帮手',
        
        // set
        'userInfo' => '用户信息',
        'profile' => '用户信息',
        'picture' => '用户图片',
        'password' => '密码',
        'password' => '密码',
        'firstName' => '名字',
        'lastName' => '姓氏',
        'selfDescript' => '自我描述',
        'myPicture' => '我的图片',
        'update' => '提交更新',
        'uploadError' => '必须是小于1M的图形文件',
        'changePassword' => '修改密码',
        'oldPassword' => '原密码',
        'newPassword' => '新密码',
        'confirmPassword' => '确认密码',
        
        // email
        'fromName' => '找帮手支持服务',
        'passwordSubject' => '找帮手密码支持服务',
        'invitationSubject' => '找帮手',
        'dear' => '你好 ',
        'passwordBody' => '，<br>重置密码的验证码在本邮件的下方。注意：仅供用户本人使用，请不要提供给任何其他人。
            <br>找帮手密码支持服务',
        'weblink' => ' <hr>了解更多请访问我们的网址  www.4helper.com',
        
        // guide info
        'welcomeUser' => '欢迎使用找帮手，只需要两步就可以开始你分享目标的新生活。',
        'step1' => '第一步，给自己添加帮手。',
        'step2' => '第二步，创建目标并分享目标。',
        'attention' => "注意：达成共识的目标不可以删除。",
        'helperAttention' => '注意：他（她）只有接受了你的邀请才能成为你的帮手。',
        'sharedGuideInfo' => '目前你还没有其他用户分享给你的目标。对于分享的目标，你可以接受，拒绝，修改，并最终提交完成或错失。
        目标只能由创建人关闭，一旦关闭的目标任何人都不能再修改，但可以在关闭目标中查询。',
        'statusOfTargets' => '目标有三种状态，每种状态下目标创建者和帮手有相应的操作权限，如下表：',
        'targetStatus' => '目标状态',
        'targetCreater' => '目标创建者',
        'helper' => '帮手',
        'disagreeTarget' => '未共识目标',
        'applyTarget' => '申请目标',
        'colorExplain' => '绿色表示进度正常，红色表示进度超期',
        'ontime' => '进度正常',
        'overtime' => '进度超期',
        'createTargetHelp' => '你可以创建目标给一个帮手或通过创建子目标给多个帮手，子目标可以多次分享给同一个帮手。',
        'oneHelperGuid' => '选择: 创建目标 --> 一个帮手 --> 选择帮手 --> 提交',
        'helpersGuid' => '选择: 创建目标 --> 多个帮手 --> 提交 --> 增加子目标 --> 选择帮手 --> 提交'
    );
} else {
    $user_labels = array(
        // top line
        'help' => 'Help',
        'logout' => 'Logout',
        'userSet' => 'Profile set',
        'version' => 'Test version',
        
        // home
        'targets' => 'Targets',
        'mylogo' => '4helper',
        'closedTargets' => 'Closed Targets',
        'helpers' => 'Helpers',
        'pleaseUpdate' => 'Please update image',
        
        // targets
        'createTargets' => 'Create Targets',
        'targetsByMe' => 'Created Targets',
        'targetsShared' => 'Shared Targets',
        'hitTarget' => 'Hit',
        'hitTargetTitle' => 'Confirmed hit Target can\'t be updated.',
        'closeTarget' => 'Close',
        'closeTargetTitle' => 'Can be checked in Closed Targets',
        'agreeTarget' => 'Agree target',
        'agreeTargetTitle' => 'Only helper can update',
        'shareTarget' => 'Shared',
        'shareTargetTitle' => 'Only helper can update shared Target',
        'modifyTarget' => 'Modified',
        'modifyTargetTitle' => 'The target was modified by helper',
        'rejectedTarget' => 'Rejected',
        'rejectedTargetTitle' => 'Sorry, helper rejected the target',
        'createrRejectTitle' => 'Helper can resubmit the rejected target',
        'missTarget' => 'Missed',
        'missTargetTitle' => 'Confirmed miss target can\'t be updated.',
        'agree' => 'Agree',
        'reject' => 'Reject',
        'delete' => 'Delete',
        'modify' => 'Modify',
        'hit' => 'Hit',
        'miss' => 'Miss',
        'addSubTarget' => 'add a sub target now...',
        'addSubTargetTitle' => 'add a sub target',
        'writeComment' => 'write a comment...',
        'send' => 'Send',
        'targetName' => 'Target Name',
        'targetEndTime' => 'Target End Time',
        'targetDescript' => 'Target Description️',
        'shareModule' => 'Share Target',
        'asignSigleMember' => 'One Helper',
        'asignMutipleMember' => 'Heplers',
        'selectReceiver' => 'Select Helper',
        'submit' => 'Submit',
        'subTarget' => 'Sub Target',
        
        // helpers
        'myHelpers' => 'My helpers',
        'helpers' => 'Helpers',
        'invitations' => 'Invitations',
        'addHelper' => 'Add helper',
        'email' => 'Email address',
        'greeting' => 'say something to helper',
        
        // set
        'userInfo' => 'Accont information',
        'profile' => 'Profile information',
        'picture' => 'Profile picture',
        'password' => 'Password',
        'firstName' => 'First Name',
        'lastName' => 'Last Name',
        'selfDescript' => 'Descript yourself',
        'myPicture' => 'My Picture',
        'update' => 'Update',
        'uploadError' => 'Only accept image file(<1m)',
        'changePassword' => 'Change password',
        'confirmPassword' => 'Confirm password',
        'oldPassword' => 'Old password',
        'newPassword' => 'New password',
        
        // email
        'fromName' => '4helper support',
        'passwordSubject' => '4helper password support',
        'invitationSubject' => 'Find Helper',
        'dear' => 'Dear ',
        'passwordBody' => ', <br>The following is the captcha of your reset password request.
                Note: Never show it to others.<br>4helper password support',
        'weblink' => ' <hr>Visit www.4helper.com for futher information',
        
        // guide info
        'welcomeUser' => 'Welcome to 4helper. There are only two steps to begin your sharing
			targets journey.',
        'step1' => 'First, add helpers for you.',
        'step2' => 'Second, create targets and share with helpers.',
        'attention' => 'Attention: Agreed targets can be deleted.',
        'helperAttention' => 'Attention: He or She can\'t be your helper untill the invition is accepted.',
        'sharedGuideInfo' => 'You don\'t have any shared targets at present. Once you get
				shared targets you can accept, reject, modify, hit or miss them. Only target\'s creater
                can close it. Close targets cannot be updated by anyone, but can be checked in history targets.',
        'statusOfTargets' => 'There are three status in targets. Each status has relative
					actions with target creater or helper.',
        'targetStatus' => 'Target status',
        'targetCreater' => 'Target creater',
        'helper' => 'Helper',
        'disagreeTarget' => 'Disagree target',
        'applyTarget' => 'Apply target',
        'colorExplain' => 'Green means on time, red means over time.',
        'ontime' => 'On time',
        'overtime' => 'Over time',
        'createTargetHelp' => 'You can create a target with one helper or helpers by creating subtargets, 
            different subtargets can be shared with same helper.',
        'oneHelperGuid' => 'choose: create targets --> one helper --> select helper --> submit',
        'helpersGuid' => 'choose: create targets --> helpers --> submit --> add a subtarget --> select helper --> submit'
    );
}










