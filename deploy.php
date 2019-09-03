<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'wordpress');

// Project repository
set('repository', 'git@github.com:cookpad-baby/hosting-wordpress.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', ['.env']);
set('shared_dirs', ['web/app/themes','web/app/uploads']);

// Writable dirs by web server
set('writable_dirs', ['web/app/uploads']);
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0777');
set('writable_chmod_recursive', false);

// Hosts
host('hosting02.babypad.local')
  ->user('deployer')
  ->set('deploy_path', '~/{{application}}');

// Tasks
task('reload:php_fpm', function () {
    run('sudo service php7.3-fpm reload');
})->desc('Reload PHP-FPM service');

task('reload:nginx', function () {
    run('sudo service nginx reload');
})->desc('Reload NGINX service');

after('success', 'reload:php_fpm');
after('success', 'reload:nginx');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
