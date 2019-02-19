/* Email template */
INSERT INTO `email_template` (`code`, `name`, `subject`, `body`) VALUES
('forgot_password_email_template', 'Forgot password mail template', 'Link to reset password', '<p>Hi <b>{recipient_name}</b>!</p><p>This is the email for resetting your password. Click the link to get your new password.</p><p>{reset_password_link}</p>'),
('activation_mail_template', 'Activation mail template', 'Activation mail', '<p>Hi <b>{recipient_name}</b>!</p><p>This is the activation email. Please click the link below to confirm your registration.</p><p>{activation_link}</p>'),
('admin_regenerate_password', 'admin regenerate password', 'Your password has been changed.', '<p>Hi <b>{recipient_name}</b>!</p><p>Your password in {url_site} has been changed by system. You can login into site with new password: <b>{new_password}</b></p>'),
('amdin_create_user_mail_template', 'Admin create user', 'Your account' ,'<p>Hi <b>{recipient_name}</b>!</p><p>Your account in {url_site} has been created.</p><p>You cant login into site with email: <b>{login_email}</b> and password: <b>{login_password}</b></p>'),
('footer', 'Footer Email', 'Footer Email', '<hr>User Federation Team'),
('invite_email_template', 'Invite Email Template', 'You are invited to {application_name}', '<p>Hi,</p>
<p>You are invited to use {application_name} application through User Federation platform</p>
<p>Please click {link_activate} to register an account and start enjoy the application.</p>
<p>You are just few clicks away to get in the {application_name} application.</p>');

/* Permission role */
INSERT INTO `permission_role`(`role_id`, `description`) VALUES 
('guest', 'Guest'),
('member', 'Member'),
('admin', 'Admin')
;
/* Permission resource */
INSERT INTO `permission_resource`(`resource_id`, `parent`, `sort_order`, `description`, `hide_in_permission_editor`) VALUES
('CommonPages', null, 3, null, 1) ,
('Payroll:CompanyNonRestful', 'CommonPages', 4, null, 1),
('HtApplication:Index', 'CommonPages', 5, null, 1),
-- ('HtApplication:Index:index', 'HtApplication:Index', 6, null, 1),
-- ('HtApplication:Index:access-denied', 'HtApplication:Index', 6, null, 1),

-- ('HtAuthentication', null, 7, 'Module Auth', 1) ,
-- ('HtAuthentication:OAuth', 'HtAuthentication', 8, 'Open Authentication', 1),

('UserIdentity:public', null, 4, null, 1) ,
('HtAuthentication:OAuth:index', 'UserIdentity:public', 8, null, 1),
('HtAuthentication:OAuth:login', 'UserIdentity:public', 8, 'Login via OAuth', 1),
('HtAuthentication:OAuth:login-callback', 'UserIdentity:public', 8, 'Login via OAuth', 1),
('HtAuthentication:OAuth:register', 'UserIdentity:public', 8, 'Register via OAuth', 1),
('HtAuthentication:OAuth:register-callback', 'UserIdentity:public', 8, 'Register via OAuth', 1),
('HtAuthentication:Logout', 'CommonPages', 8, null, 1),
-- ('HtAuthentication:Internal', 'UserIdentity:public', 8, 'Internal authentication', 1),
('HtAuthentication:Internal:login', 'UserIdentity:public', 9, 'Login', 1),
('HtAuthentication:Internal:activate', 'UserIdentity:public', 9, 'Activate internal login', 1),
('HtAuthentication:Internal:register', 'UserIdentity:public', 9, 'Register', 1),
('HtAuthentication:Internal:validate-unique-field', 'UserIdentity:public', 9, null, 1),

('HtUser:Invite:activate', 'UserIdentity:public', 10, 'Activate', 1),
('HtUser:Invite:activate-oauth', 'UserIdentity:public', 10, 'Activate OAuth', 1),
('HtUser:Invite:activate-oauth-callback', 'UserIdentity:public', 10, 'Activate OAuth', 1),
('HtUser:Invite:register-with-invitation-code', 'UserIdentity:public', 10, 'Register with invitaion code', 1),

('UserIdentity:private', null, 4, 'User Identity', 1) ,
('HtAuthentication:OAuth:add-login', 'UserIdentity:private', 8, 'Add login', 1),
('HtAuthentication:OAuth:add-login-callback', 'UserIdentity:private', 8, 'Open Authentication', 1),
('HtAuthentication:OAuth:delete-login', 'UserIdentity:private', 8, 'Delete login', 1),
('HtAuthentication:Internal:add', 'UserIdentity:private', 9, 'Add internal login', 1),
('HtAuthentication:Internal:delete', 'UserIdentity:private', 9, 'Delete internal login', 1),
('HtAuthentication:Internal:update', 'UserIdentity:private', 9, 'Change password', 1),
('HtAuthentication:AuthenticationAccount', 'UserIdentity:private', 10, 'View identities', 0),

('UserProfile', null, 4, null, 1) ,
('HtUser:Profile', 'UserProfile', 9, 'Update User Profile', 0),

('UserAccess', null, 4, null, 1) ,
('HtUser:Access', 'UserAccess', 9, 'User Access', 0),

('API', null, 4, null, 1) ,
('HtAuthentication:Token', 'API', 8, null, 1),
('HtAuthentication:ApiOrganisation', 'API', 8, null, 1),
('HtAuthentication:ApiUser', 'API', 7, null, 1),
('HtUser:Invite:invite', 'API', 10, 'Invite', 1),

('Admin', null, 14,'Administration', 1),

('Admin:User', 'Admin', 15, 'Manage User', 0),
('HtAdmin:UserRest', 'Admin:User', 16, 'Manager user via Rest API', 1),
('HtAdmin:DeletedUserRest', 'Admin:User', 16, 'Manager deleted user via Rest API', 1),

('HtAdmin:User:index', 'Admin:User', 16, 'View users', 0),
('HtAdmin:User:list', 'HtAdmin:User:index', 17, 'View list via Ajax', 1),
('HtAdmin:UserApplication:get-user', 'HtAdmin:User:index', 17, 'Get Users for XmlHttpRequest', 1),
('HtAdmin:UserOrganisation:get-user', 'HtAdmin:User:index', 17, 'Get org\'s users for XmlHttpRequest', 1),
('HtAdmin:UserRole:get-user', 'HtAdmin:User:index', 17, 'Get user for add user to role action', 1),

('HtAdmin:User:create', 'Admin:User', 16, 'Create user', 0),

('HtAdmin:User:delete', 'Admin:User', 16, 'Delete user', 0),
('HtAdmin:User:deleted-users', 'HtAdmin:User:delete', 17, 'Delete users', 1),
('HtAdmin:User:delete-multiple', 'HtAdmin:User:delete', 17, 'Delete multiple user', 1),

('HtAdmin:User:permanently-delete', 'Admin:User', 16, 'Permanently delete user', 0),

('HtAdmin:User:edit', 'Admin:User', 16, 'Edit user', 0),
('HtAdmin:User:generate-password', 'HtAdmin:User:edit', 17, 'Generate password', 1),

('HtAdmin:User:restore', 'Admin:User', 16, 'Restore user', 0),
('HtAdmin:User:restore-multiple', 'HtAdmin:User:restore', 17, 'Restore multiple users', 1),

('HtAdmin:UserApplication:view-user', 'Admin:User', 16, 'View user\'s apps', 0),
('HtAdmin:UserApplication:delete-app', 'Admin:User', 16, 'Delete user\'s app', 0),
('HtAdmin:UserApplication:add-app', 'Admin:User', 16, 'Add user\'s app', 0),

('HtAdmin:UserRole:user', 'Admin:User', 16, 'View user\'s role', 0),
('HtAdmin:UserRole:add-role', 'Admin:User', 16, 'Assign user\'s role', 0),
('HtAdmin:UserRole:delete', 'Admin:User', 16, 'Delete user\'s role', 0),

('HtAdmin:UserIdentity:index', 'Admin:User', 16, 'View user\'s identity', 0),
('HtAdmin:UserIdentity:delete', 'Admin:User', 16, 'Delete user\'s identity', 0),

('HtAdmin:UserOrganisation:add-org', 'Admin:User', 16, 'Add user\'s org', 0),
('HtAdmin:UserOrganisation:delete-org', 'Admin:User', 16, 'Delete user\'s org', 0),
('HtAdmin:UserOrganisation:view-user', 'Admin:User', 16, 'View user\'s orgs', 0),

('HtAdmin:Log', 'Admin', 15, 'View Log', 0),

('Admin:Organisation', 'Admin', 15, 'Organisation', 0),
('HtAdmin:Organisation:create', 'Admin:Organisation', 16, 'Create org', 0),
('HtAdmin:Organisation:edit', 'Admin:Organisation', 16, 'Edit org', 0),
('HtAdmin:Organisation:delete', 'Admin:Organisation', 16, 'Delete org', 0),

('HtAdmin:Organisation:index', 'Admin:Organisation', 16, 'View orgs', 0),
('HtAdmin:UserOrganisation:get-organisation', 'HtAdmin:Organisation:index', 17, 'Get Orgs for XmlHttpRequest', 1),
('HtAdmin:Organisation:get-org', 'HtAdmin:Organisation:index', 17, 'Get orgs for XmlHttpRequest', 1),

('HtAdmin:ApplicationOrganisation:add-application', 'Admin:Organisation', 16, 'Add org\'s app', 0),
('HtAdmin:ApplicationOrganisation:delete-application', 'Admin:Organisation', 16, 'Delete org\'s app', 0),
('HtAdmin:ApplicationOrganisation:view-org', 'Admin:Organisation', 16, 'View org\'s apps', 0),

('HtAdmin:UserOrganisation:add-user', 'Admin:Organisation', 16, 'Add org\'s user', 0),
('HtAdmin:UserOrganisation:delete-user', 'Admin:Organisation', 16, 'Delete org\'s user', 0),
('HtAdmin:UserOrganisation:view-org', 'Admin:Organisation', 16, 'View org\'s users', 0),

('Admin:Application', 'Admin', 15, 'Application', 0),
('HtAdmin:Application:index', 'Admin:Application', 16, 'View apps', 0),
('HtAdmin:ApplicationOrganisation:get-application', 'HtAdmin:Application:index', 17, 'Get application for XmlHttpRequest', 1),

('HtAdmin:Application:edit', 'Admin:Application', 16, 'Edit app', 0),
('HtAdmin:Application:delete', 'Admin:Application', 16, 'Delete app', 0),
('HtAdmin:Application:create', 'Admin:Application', 16, 'Create app', 0),

('HtAdmin:ApplicationServer:add-server', 'Admin:Application', 16, 'Add app\'s server', 0),
('HtAdmin:ApplicationServer:delete-server', 'Admin:Application', 16, 'Delete app\'s server', 0),
('HtAdmin:ApplicationServer:servers', 'Admin:Application', 16, 'View app\'s servers', 0),

('HtAdmin:UserApplication:view-app', 'Admin:Application', 16, 'View app\'s users', 0),
('HtAdmin:UserApplication:delete-user', 'Admin:Application', 16, 'Delete app\'s user', 0),
('HtAdmin:UserApplication:add-user', 'Admin:Application', 16, 'Add app\'s user', 0),

('HtAdmin:ApplicationOrganisation:add-org', 'Admin:Application', 16, 'Add app\'s org', 0),
('HtAdmin:ApplicationOrganisation:delete-org', 'Admin:Application', 16, 'Delete app\'s org', 0),
('HtAdmin:ApplicationOrganisation:view-app', 'Admin:Application', 16, 'View app\'s orgs', 0),

('Admin:Server', 'Admin', 15, 'Server', 0),
('HtAdmin:Server:index', 'Admin:Server', 16, 'View servers', 0),
('HtAdmin:UserApplication:get-server', 'HtAdmin:Server:index', 17, 'Get Servers for XmlHttpRequest', 1),
('HtAdmin:Organisation:get-server', 'HtAdmin:Server:index', 17, 'Get servers for XmlHttpRequest', 1),
('HtAdmin:ApplicationOrganisation:get-server', 'HtAdmin:Server:index', 17, 'Get servers for XmlHttpRequest', 1),
('HtAdmin:ApplicationOrganisation:get-server-org', 'HtAdmin:Server:index', 17, 'Get servers by orgId for XmlHttpRequest',1),
('HtAdmin:ApplicationServer:get-server', 'HtAdmin:Server:index' , 17, 'Get server for add server to app for XmlHttpRequest', 1),

('HtAdmin:Server:create', 'Admin:Server', 16, 'Create server', 0),
('HtAdmin:Server:edit', 'Admin:Server', 16, 'Edit server', 0),
('HtAdmin:Server:delete', 'Admin:Server', 16, 'Delete server', 0),

('Admin:Role', 'Admin', 15, 'Role', 0),
('HtAdmin:Role:index', 'Admin:Role', 16, 'View roles', 0),
('HtAdmin:Role:edit-permission', 'Admin:Role', 16, 'Edit role\'s permission', 0),
('HtAdmin:Role:edit', 'Admin:Role', 16, 'Edit role', 0),
('HtAdmin:Role:create', 'Admin:Role', 16, 'Create role', 0),
('HtAdmin:Role:delete', 'Admin:Role', 16, 'Delete role', 0),
('HtAdmin:UserRole:role', 'Admin:Role', 16, 'View role\'s user', 0),
('HtAdmin:UserRole:delete-user', 'Admin:Role', 16, 'Delete user', 0),
('HtAdmin:UserRole:add-user', 'Admin:Role', 16, 'Add user to role', 0)
;
/* Permission acl */
INSERT INTO `permission_acl` (`role_id`, `resource_id`, `access`, `priviledges`, `assertion_class`, `sort_order`) VALUES
(NULL, 'CommonPages', 1, NULL, NULL, 1),
(NULL, 'API', 1, NULL, NULL, 1),
(NULL, 'UserIdentity:public', 1, NULL, NULL, 7),

('member', 'UserIdentity:private', 1, NULL, NULL, 8),
('member', 'UserProfile', 1, NULL, NULL, 8),
('member', 'UserAccess', 1, NULL, NULL, 8),

('admin', 'Admin', 1, NULL, NULL, 11),
('admin', 'UserIdentity:private', 1, NULL, NULL, 11),
('admin', 'UserProfile', 1, NULL, NULL, 11),
('admin', 'UserAccess', 1, NULL, NULL, 11)
;

INSERT INTO `application` (`application_id`, `title`, `description`, `creation_date`, `last_updated`) VALUES 
(1, 'Payroll', 'Payroll', '2013-11-19', NULL),
(2, 'Accounting', 'Accounting', '2013-11-19', NULL),
(3, 'HR', 'HR', '2013-11-19', NULL);

INSERT INTO `user` (`user_id`, `firstname`,`lastname`, `email`, `is_enabled`) VALUES
(1,'admin', 'Administrator', 'admin@hometradies.com', 1);

INSERT INTO `authentication_account` (`authentication_account_id`,`user_id`, `type`) VALUES 
(1, 1, 'internal');

INSERT INTO `authentication_internal`(`authentication_internal_id`, `authentication_account_id`, `username`, `password`, `is_activated`) VALUES
(1, 1, 'admin@hometradies.com', '7c4a8d09ca3762af61e59520943dc26494f8941b', 1);

INSERT INTO `permission_user_role` (`user_id`, `role_id`) VALUES
(1, 'admin');