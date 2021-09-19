from __future__ import print_function

import getpass
import socket
import sys
import os
from os.path import join, expanduser, dirname
from time import sleep

from fabric.api import (
    local, task, env, sudo, run, cd, prompt, execute, put)
from fabric.colors import green, red
from fabric.context_managers import hide
from fabric.contrib.console import confirm
from fabric.contrib.files import upload_template
from fabric.operations import open_shell
from gumstix.fabtools import (
    Apt, aws, install_crontab, php, symfony, which, hosts,
    generate_secret, Nginx, fatal, Git, server, MySQL)
# noinspection PyUnresolvedReferences
from gumstix.fabtools import (set_hostname, tag_release)
from gumstix.fabtools.app import App
from gumstix.fabtools.hosts import (
    Localhost, Ec2Host, Cloud, Host)
from gumstix.fabtools.load_balancing import (
    register_target, deregister_target, describe_target_group)
from gumstix.fabtools.password import (
    PasswordDelegator, IniPasswordManager,
    EnvPasswordManager, PromptPasswordManager,
    AwsDictionaryPasswordManager, ensure_application_key
)
from gumstix.fabtools.symfony import Composer, Console, delete_cache

##############
## Settings ##
##############

IAM_ROLE_LIVE = 'arn:aws:iam::915096417572:instance-profile/rialto'
GROUP_LIVE_RIALTO = 'sg-cea0e8ab'

IAM_ROLE_DEV = 'arn:aws:iam::915096417572:instance-profile/dev_rialto'
GROUP_DEV_RIALTO = 'sg-b42db7d1'

TARGET_GROUP_DEV = 'arn:aws:elasticloadbalancing:us-west-2:915096417572:targetgroup/dev-rialto/9889212051320fd0'
TARGET_GROUP_LIVE = 'arn:aws:elasticloadbalancing:us-west-2:915096417572:targetgroup/live-rialto/76ee33efcd0d7f8d'

REPO_URL = 'git@bitbucket.org:gumstix/rialto.git'
CLONE_TARGET = 'rialto'

REQUIRED_PACKAGES = [
    'build-essential',  # for compiling PECL packages
    'curl',
    'ghostscript',  # for reading non-text PDFs (OCR)
    'gnumeric',  # provides `ssconvert` tool for xls to csv conversion
    'imagemagick',
    'mysql-client',
    'nginx',
    'pgf',  # Latex package for graphics
    'php7.2',
    'php7.2-bcmath',
    'php7.2-cli',
    'php7.2-curl',
    'php7.2-dev',  # for compiling PECL packages
    'php7.2-fpm',
    'php7.2-gd',
    'php7.2-gmp',
    'php7.2-imap',
    'php7.2-intl',
    'php7.2-json',
    'php7.2-mbstring',
    'php7.2-mongodb',
    'php7.2-mysql',
    'php-oauth',  # for SSO
    'php-pear',
    'php-ps',
    'php7.2-soap',
    'php7.2-xmlrpc',
    'php7.2-xml',
    'php7.2-zip',
    'poppler-utils',
    'pslib-dev',
    'pv',
    'python-pip',  # for database backups
    'python-setuptools',  # for database backups
    'qrencode',
    'supervisor',  # for JMS job bundle
    'tesseract-ocr',
    'texlive-fonts-recommended',
    'texlive-latex-extra',
]

# A list of "safe" tables without sensitive data.
SAFE_TABLES = [
    'AccountGroups',
    'Areas',
    'BOM',
    'BankAccounts',
    'BankStatementPattern',
    'CATaxRegimes',
    'COGSGLPostings',
    'ChartMaster',
    'CmsEntry',
    'Companies',
    'ComponentConnections',
    'County',
    'Currencies',
    'Customization',
    'CustomizationToSubstitution',
    'database_migration',
    'DiscountGroup',
    'DiscountRate',
    'Documentation',
    'Forms',
    'Geography_Address',
    'GumstixSSO_Credential',
    'HoldReasons',
    'ItemVersion',
    'Locations',
    'Magento2_Storefront',
    'Manufacturer',
    'Names',
    'PaymentMethod',
    'PaymentMethodGroup',
    'PaymentTerms',
    'Periods',
    'Prices',
    'Printer',
    'Product',
    'ProductMarketingInfo',
    'PurchData',
    'PurchOrders',
    'PurchasingCost',
    'PurchasingDataTemplate',
    'Role',
    'SalesGLPostings',
    'SalesTypes',
    'Salesman',
    'Security_SsoLink',
    'ShipmentProhibition',
    'Shippers',
    'ShippingMethod',
    'Shipping_HarmonizationCode',
    'StandardCost',
    'Stock_BinStyle',
    'Stock_Rack',
    'Stock_Shelf',
    'Stock_ShelfPosition',
    'Stock_Shelf_BinStyle',
    'StockCategory',
    'StockFlags',
    'StockItemFeature',
    'StockItemToDiscountGroup',
    'StockLevelStatus',
    'StockMaster',
    'StockProducer',
    'StockSerialItems',
    'StockStatus',
    'Substitutions',
    'SupplierApi',
    'SupplierContacts',
    'SupplierInvoice',
    'SupplierInvoicePattern',
    'Suppliers',
    'SysTypes',
    'TaxAuthLevels',
    'TaxAuthorities',
    'TaxRegime',
    'TurnkeyExclusions',
    'UserRole',
    'WorkType',
    'WWW_Users',
]


##################
## Environments ##
##################

env.hosts = []  # Force user to choose an environment
env.app = App('rialto')
env.forward_agent = True
env.default_theme = 'claro'
env.db_user = 'rialto'
env.key_name = '{}@{}'.format(getpass.getuser(), socket.gethostname())
env.timezone = 'America/Vancouver'


@task
def localhost():
    """
    Execute tasks on the local machine.
    """
    env.user = getpass.getuser()
    env.app_env = 'dev'
    env.pw_manager = PasswordDelegator([
        IniPasswordManager('rialto.ini', 'local', ignore_io_errors=True),
        EnvPasswordManager(),
        PromptPasswordManager(),
    ])
    env.db_admin = MySQL(host=env.pw_manager.get('db_host'),
                         name=env.pw_manager.get('db_name'),
                         user=env.pw_manager.get('db_admin_user'),
                         password=env.pw_manager.get('db_admin_password'))
    env.db_fromhost = 'localhost'
    env.db_app_password = env.pw_manager.get('db_app_password')
    env.db_user = env.pw_manager.get('db_app_user')
    env.appdir = dirname(__file__)
    env.basedir = dirname(env.appdir)
    env.server = Localhost(env.app, env.basedir)
    env.server.activate(env)
    env.console = Console(env.appdir, env.app_env)
    env.git = Git(REPO_URL, env.appdir, branch='master')
    env.logdir = env.pw_manager.get('logdir')
    env.cfgdir = env.pw_manager.get('cfgdir')
    env.composer = Composer(
        dirname(local('which composer', capture=True)), env.appdir, env.app_env)

    env.uuids = {
        '8cde4d00-1e99-4a34-b8c4-a5132a4d20d1': 'madison',
        '9d143fa2-4e92-4deb-8286-44cb998eaf5c': 'geppetto',
        '73fa9df8-44f5-4c13-aa0b-be0530040cb7': 'orocrm',
        'c25fb631-b930-4033-83cc-db114dd70cec': 'catalina',
    }


@task
def celine():
    """
    Celine's dev environment.
    """
    localhost()
    # where apache log files should go
    env.logdir = '/home/celine/log'
    # where apache config files should go
    env.cfgdir = '/home/celine/etc'
    # db admin user name
    env.test_email = 'celine.barrozo@gumstix.com'
    env.composer = Composer('/home/celine/bin', env.appdir, env.app_env)
    env.pw_manager = PasswordDelegator([
        EnvPasswordManager(),
        PromptPasswordManager(),
    ])
    env.uuids['044c07a5-63bb-45e9-947d-b7e7224bc56c'] = 'celine.barrozo'


@task
def john():
    """
    John's dev environment.
    """
    localhost()
    # where apache log files should go
    env.logdir = '/home/john/log'
    # where apache config files should go
    env.cfgdir = '/home/john/etc'
    # db admin user name
    env.test_email = 'john.chow@gumstix.com'
    env.composer = Composer('/home/john/bin', env.appdir, env.app_env)
    env.pw_manager = PasswordDelegator([
        EnvPasswordManager(),
        PromptPasswordManager(),
    ])


@task
def gordon():
    """
    Gordon's dev environment.
    """
    localhost()
    # where apache log files should go
    env.logdir = '/home/gordon/log'
    # where apache config files should go
    env.cfgdir = '/home/gordon/etc'
    # db admin user name
    env.test_email = 'gordon@gumstix.com'
    env.composer = Composer('/home/gordon/bin', env.appdir, env.app_env)
    env.pw_manager = PasswordDelegator([
        EnvPasswordManager(),
        IniPasswordManager('~/passwords/rialto.ini', 'local'),
        PromptPasswordManager(),
    ])


def ec2_instance(server):
    """
    Sets up environment variables common to AWS EC2 instances.

    :type server : Ec2Host
    """
    env.disable_known_hosts = True  # ec2 instances are always changing
    env.basedir = server.basedir
    env.user = 'ubuntu'
    env.appdir = join(env.basedir, CLONE_TARGET)
    env.cfgdir = join(env.basedir, 'etc')
    env.logdir = '/var/log'
    env.db_fromhost = '%'


@task
def dev_ec2():
    """
    Configure the Fabric environment for devstix.
    """
    env.server = Ec2Host(env.app, Cloud.dev(), IAM_ROLE_DEV)
    env.server.add_security_group(GROUP_DEV_RIALTO)
    env.server.image = aws.IMAGE_UBUNTU_1604
    ec2_instance(env.server)
    env.pw_manager = AwsDictionaryPasswordManager('dev/rialto/secrets')
    env.db_admin = MySQL(
        host='dev-rialto-rds.c9kpdp26dpn1.us-west-2.rds.amazonaws.com',
        name='rialto',
        user='admin',
        password=env.pw_manager.get('db_admin_password'))
    env.mongo_host = '10.1.21.106'
    env.app_env = 'stage'
    env.test_email = 'jesse.ludtke@gumstix.com'
    env.composer = Composer(join(env.basedir, 'bin'), env.appdir, env.app_env)
    env.console = Console(env.appdir, env.app_env)
    env.git = Git(REPO_URL, env.appdir, branch='master')
    # UUIDs from https://accounts.devstix.com/admin/client_api/ssouser/
    env.uuids = {
        'c25fb631-b930-4033-83cc-db114dd70cec': 'catalina',
        # '048d469a-f49f-44b0-8bf2-65e45081e019': 'celine.barrozo',
        '9d143fa2-4e92-4deb-8286-44cb998eaf5c': 'geppetto',
        '790d18da-c2fa-46b5-8e6f-fdd48ec45850': 'ianfp',
        # 'afc155be-944c-44c2-8a65-809299fa749f': 'keith.lee',
        '8cde4d00-1e99-4a34-b8c4-a5132a4d20d1': 'madison',
        '73fa9df8-44f5-4c13-aa0b-be0530040cb7': 'orocrm',
    }
    env.keyname = 'dev-rialto'
    ensure_application_key('dev/rialto/ssh', env.keyname)
    env.key_filename = [join(expanduser('~/.ssh'), env.keyname)]
    env.target_group = TARGET_GROUP_DEV


@task
def devstix():
    """
    Execute tasks on the devstix environment.
    """
    dev_ec2()
    env.server.activate(env)


@task
def live_ec2():
    """
    Configure the Fabric environment for live.
    """
    env.server = Ec2Host(env.app, Cloud.live(), IAM_ROLE_LIVE)
    env.server.add_security_group(GROUP_LIVE_RIALTO)
    env.server.image = aws.IMAGE_UBUNTU_1604
    ec2_instance(env.server)
    env.git = Git(REPO_URL, env.appdir, branch='live')
    env.app_env = 'prod'
    env.pw_manager = AwsDictionaryPasswordManager('live/rialto/secrets')
    env.db_admin = MySQL(
        host='live-rialto-rds.c9kpdp26dpn1.us-west-2.rds.amazonaws.com',
        name='rialto',
        user='admin',
        password=env.pw_manager.get('db_admin_password'))
    env.mongo_host = '10.0.1.157'
    env.composer = Composer(join(env.basedir, 'bin'), env.appdir, env.app_env)
    env.console = Console(env.appdir, env.app_env)
    env.git = Git(REPO_URL, env.appdir, branch='live')
    env.keyname = 'live-rialto'
    ensure_application_key('live/rialto/ssh', env.keyname)
    env.key_filename = [join(expanduser('~/.ssh'), env.keyname)]
    env.target_group = TARGET_GROUP_LIVE


@task
def live():
    """
    Execute tasks on the live production server.
    """
    live_ec2()
    env.server.activate(env)


@task
def launch():
    """
    Launch a new EC2 instance.
    """
    host = env.server  # type: Host
    host.assert_remote()
    hosts.launch(env.server, env.keyname)


@task
def stop():
    """
    Stop a running EC2 instance.
    """
    host = env.server  # type: Host
    host.assert_remote()
    aws.stop_instance(host.name)


@task
def terminate():
    """
    Terminate a stopped EC2 instance.
    """
    host = env.server  # type: Host
    host.assert_remote()
    aws.terminate_instance(host.name)


###########
## Tasks ##
###########

@task
def test_ssh():
    """
    Test the SSH connection to the server.
    """
    run('hostname')
    run('ls -a ~')


@task
def bootstrap():
    """
    Initial setup tasks for a new remote server.
    """
    host = env.server  # type: Host
    server.bootstrap(host.name)


@task
def setup():
    """
    Full system setup.
    """
    execute(ppas)
    execute(packages)
    execute(php_setup)
    execute(clone)
    execute(parameters)
    execute(db_grant)
    execute(composer_install)
    if env.server.is_local:
        msg = "Please enter the path of your database dump file" \
              " (leave blank to skip): "
        dumpfile = prompt(msg)
        if dumpfile != '':
            execute(db_load, dumpfile)
    execute(nginx)
    execute(assets)
    execute(supervisor)
    if env.server.is_dev:
        execute(sso_setup)
    # Important so we don't have two live servers running background jobs!
    execute(jobs_stop)
    execute(crontab)
    execute(cache_clear)
    execute(migrate)
    execute(whats_next)


@task
def ppas():
    """
    Install required system-wide PPAs.
    """
    apt = Apt()
    apt.add_repository('ppa:ondrej/php')


@task
def packages():
    """
    Install system-wide packages required by Rialto.
    """
    apt = Apt()
    apt.install(REQUIRED_PACKAGES, recommends=False)
    php.set_default_alternative('/usr/bin/php7.2')
    apt.purge(['javascript-common'])
    if apt.os_version == '16.04':
        php.pecl('ps')
    execute(enable_modules)


@task
def enable_modules():
    """
    Enable required PHP modules.
    """
    php.enmod('imap')
    php.enmod('ps')


@task
def clone():
    """
    Clone the Rialto repo onto the remote server.
    """
    env.git.clone()


@task
def php_setup():
    """
    Install and configure PHP-FPM.
    """
    s = php.Settings()
    s.set('date.timezone', 'America/Los_Angeles')
    s.apply()

    fpm = php.Fpm()
    fpm.setup()


@task
def parameters():
    """
    Create/update the Symfony parameters.yml file.
    """
    db = env.db_admin  # type: MySQL
    symfony.parameters(join(env.appdir, 'app/config'), {
        'db_password': env.pw_manager.get('db_app_password'),
        'secret': generate_secret(),
        'sentry_dsn': env.pw_manager.get('sentry_dsn'),
        'email.password': env.pw_manager.get('email_password'),
        'assets_version': env.git.get_short_hash(),
        'rialto_email.xmpp_password': env.pw_manager.get('xmpp_password'),
        'rialto_purchasing.octopart_catalog_apikey': env.pw_manager.get(
            'octopart_key'),
        'rialto_purchasing.supplier_mailbox_password': env.pw_manager.get(
            'supplier_mailbox_password'),
        'ups.access_license': env.pw_manager.get('ups_access_license'),
        'ups.password': env.pw_manager.get('ups_password'),
        'ups.invoice_password': env.pw_manager.get('ups_invoice_password'),
        'authorizenet.trans_key': env.pw_manager.get('authnet_transkey'),
        'rialto_wordpress.password': env.pw_manager.get('wordpress_password'),
        'taxjar_api_token': env.pw_manager.get('taxjar_token'),
        'ciiva_apikey': env.pw_manager.get('ciiva_key'),
        'ciiva_password': env.pw_manager.get('ciiva_password'),
        'pcb_ng.api.user': env.pw_manager.get('pcb_ng_user'),
        'pcb_ng.api.password': env.pw_manager.get('pcb_ng_password')
    })


@task
def merge(test='y'):
    """
    Merge changes from the master branch into the live branch.

    @param test: run test suite?
    """
    if test != 'n':
        execute(phpunit)
    local('git checkout live')
    local('git merge master')
    local('git push origin live master')
    local('git checkout master')


@task
def phpunit():
    """
    Run PHPUnit test suite.
    """
    local('composer phpstan')
    local('composer phpunit')


@task
def assets():
    """
    Install javascript files, etc, into the web/ directory.
    """
    local('rm -rf node_modules')  # clean slate
    local('npm ci')
    execute(assets_js)
    execute(assets_css)
    execute(assets_encore)
    execute(delete_cache)
    env.console.run('assets:install --symlink', user=env.user)
    execute(delete_cache)


@task
def assets_js():
    js = {
        'angular.js': 'angular/angular.js',
        'jquery/jquery.js': 'jquery/dist/jquery.js',
        'jquery-ui/jquery-ui.js': 'jquery-ui-dist/jquery-ui.js',
        'jquery.tablesorter/jquery.tablesorter.js': 'tablesorter/dist/js/jquery.tablesorter.min.js',
        'lodash.js': 'restangular/node_modules/lodash/lodash.js',
        'restangular.js': 'restangular/dist/restangular.js',
        'select2/select2.js': 'select2/dist/js/select2.js',
    }
    for dst, src in js.items():
        src_path = join(dirname(__file__), 'node_modules', src)
        dst_path = join(env.appdir, 'web/js/vendor', dst)
        run('mkdir -p {}'.format(dirname(dst_path)))
        put(src_path, dst_path)


@task
def assets_css():
    css = {
        'jquery-ui/jquery-ui.css': 'jquery-ui-dist/jquery-ui.min.css',
        'jquery-ui/images/': 'jquery-ui-dist/images/*',
        'select2.css': 'select2/dist/css/select2.min.css',
    }
    for dst, src in css.items():
        src_path = join(dirname(__file__), 'node_modules', src)
        dst_path = join(env.appdir, 'web/css/build', dst)
        run('mkdir -p {}'.format(dirname(dst_path)))
        put(src_path, dst_path)

    our_css = 'web/css/build/rialto.css'
    local('./node_modules/.bin/lessc app/Resources/public/less/rialto.less {}'.format(our_css))
    if env.server.is_remote:
        put(our_css, join(env.appdir, our_css))


@task
def assets_encore():
    if env.server.is_live:
        local('npx encore production')
    else:
        local('npx encore dev')

    to_upload = [
        'web/build'
    ]
    localdir = dirname(__file__)
    for path in to_upload:
        src = join(localdir, path, '*')
        dst = join(env.appdir, path)
        run('mkdir -p {}'.format(dst))
        put(local_path=src, remote_path=dst)


@task
def down():
    """
    Take the site down for maintenance.

    The Apache config looks for this maintenance.trigger file and if it exists,
    returns a 503 response.
    """
    execute(jobs_stop)
    run('touch {}/maintenance.trigger'.format(env.appdir))


@task
def up():
    """
    Bring the site back up from maintenance.
    """
    run('rm -f {}/maintenance.trigger'.format(env.appdir))
    execute(jobs_start)


@task
def jobs_stop():
    """
    Turn off all background supervisor and cron jobs.
    """
    sudo('service cron stop', warn_only=True)
    sudo('service supervisor stop', warn_only=True)


@task
def jobs_start():
    """
    Enable all background supervisor and cron jobs.
    """
    execute(jobs_restart)


@task
def jobs_restart():
    """
    Restart background jobs.
    """
    sudo('service supervisor restart', warn_only=True)
    sudo('service cron restart', warn_only=True)
    execute(jobs_status, 2)


@task
def jobs_status(sleeptime=0):
    """
    Check the status of background job processes.
    """
    sleep(int(sleeptime))
    sudo('supervisorctl status', warn_only=True)
    run('ps -ef | grep [c]ron', warn_only=True)


@task
def server_restart():
    """
    Restart the web server application.
    """
    php.Fpm().restart()
    sudo('service nginx restart', warn_only=True)


@task
def pull():
    """
    Updates the code to the latest version.
    """
    env.git.pull()
    execute(cache_clear)


@task
def update_all():
    """
    Updates the code, dependencies, and assets to the latest versions.
    """
    env.git.pull()
    execute(composer_install)
    execute(assets)
    execute(migrate)
    execute(cache_clear)


@task
def release():
    """
    Final activation of a new live server.
    """
    env.server.assert_live()
    execute(jobs_restart)
    execute(tag_release)


@task
def composer_install():
    """
    Install all required PHP dependencies using Composer.
    """
    execute(delete_cache)
    env.composer.install()


@task
def cache_clear():
    execute(delete_cache)
    env.console.run('cache:clear --no-warmup')
    execute(server_restart)


@task
def cache_warm():
    env.console.run('cache:warmup')


@task
def migrate():
    """
    Execute a database migration script.
    """
    env.console.run('doctrine:migrations:status --show-versions')
    if not confirm("Continue?", True):
        return
    if env.server.is_live and confirm("Back up live database?", default=True):
        execute(db_dump)
        env.console.run('doctrine:migrations:status --show-versions')
    msg = "Migrate to which version? (leave blank for latest) "
    version = prompt(msg, default='')
    env.console.run('doctrine:migrations:migrate {}'.format(version))


@task
def db_grant():
    """
    Grant database permissions to the Rialto application.
    """
    env.db_admin.create()
    pw = env.pw_manager.get('db_app_password')
    privileges = [
        'select',
        'insert',
        'update',
        'delete',
        'lock tables',
        'create',
        'create temporary tables',
        'drop',
        'alter',
        'index',
        'references',
    ]
    with hide('running'):
        env.db_admin.grant(privileges, env.db_user, pw, env.db_fromhost)


@task
def dbshell():
    """
    Open a database console as the database admin.
    """
    cmd = "mysql -h {db.host} -u {db.user} '-p{db.password}' {db.name}"
    with hide('running'):
        open_shell(command=cmd.format(db=env.db_admin))


@task
def app_console(cmd=''):
    """
    Run a Symfony app/console command.
    """
    env.console.run(cmd)


@task
def test_db():
    """
    LOCAL ONLY: create a test database.
    """
    require_dev()
    dumpfile = expanduser('~/tmp/rialto_test.dmp')
    env.db_admin.dump_schema(dumpfile)
    local(r"sed -i 's/ENGINE=InnoDB/ENGINE=Memory/g' {}".format(dumpfile))
    disallowed_types = [
        'longtext',
        'mediumtext',
        'tinytext',
        ' text',
        'longblob',
        'mediumblob',
        ' blob',
    ]
    for type in disallowed_types:
        local(r"sed -i 's/{}/varchar(1000)/g' {}".format(type, dumpfile))
    test_db = MySQL(host='localhost',
                    name='rialto_test',
                    user=env.db_admin.user,
                    password=env.db_admin.password)
    test_db.grant(['all'], env.db_user, env.db_app_password, 'localhost')
    test_db.drop()
    test_db.create()
    test_db.load(dumpfile)


@task
def clean_db():
    """
    LOCAL ONLY: create a clean database for developers.
    """
    require_dev()
    dumpfile = expanduser('~/tmp/rialto_clean.dmp')
    env.db_admin.dump(dumpfile, flags=('--no-data',))
    env.db_admin.dump_table(dumpfile, ' '.join(SAFE_TABLES))


@task
def lockfiles():
    """
    LOCAL ONLY: set lockfile permissions.
    """
    local('sudo chmod 666 /tmp/*lockfile')


@task
def nginx():
    """
    Install and configure Nginx and PHP-FPM.
    """
    setup = Nginx(env.app.name, {
        'domain': env.server.domain,
        'appdir': env.appdir,
        'cfgdir': env.cfgdir,
        'logdir': env.logdir,
        'app_env': env.app_env,
        'aws_key': env.pw_manager.get('aws_key'),
        'aws_secret': env.pw_manager.get('aws_secret'),
        'https': 'on' if env.server.is_https else '',
    })
    execute(setup)


@task
def sso_setup():
    """
    Create the Single Sign-On (SSO) credential.
    """
    with cd(env.appdir):
        key = env.pw_manager.get('sso_key')
        secret = env.pw_manager.get('sso_secret')
        execute(delete_cache)
        env.console.run('gumstix-sso:setup {} {}'.format(key, secret))
        execute(delete_cache)


@task
def cloc():
    """
    Count lines of code in the project.
    """
    include = [
        'app',
        'src',
        'web',
    ]
    exclude = [
        'app/data',
        'web/bundles',
        'web/js/vendor',
    ]
    local('cloc --exclude-dir={exc} {inc}'.format(
        inc=' '.join(include),
        exc=','.join(exclude)
    ))


@task
def db_dump():
    """
    Back up the database into a timestamped dump file.
    """
    require_live()
    loader = aws.DatabaseBackup(env.app.name)
    loader.dump(env.db_admin)


@task
def db_refresh():
    """
    DEV ONLY: download and install the most recent database backup.
    """
    require_dev()
    loader = aws.DatabaseBackup(env.app.name)
    dumpfile = loader.download()
    db_load(dumpfile)


@task
def db_load(dumpfile):
    """
    DEV ONLY: load a database backup file.
    """
    require_dev()
    db = env.db_admin
    db.refresh(dumpfile)
    execute(db_post_refresh)


@task
def db_post_refresh():
    """
    DEV ONLY: run post-refresh database tasks.
    """
    env.server.assert_dev()
    db = env.db_admin
    load_dev_fixtures(db)
    execute(delete_cache)
    execute(migrate)
    execute(sso_setup)


@task
def db_download():
    """
    DEV ONLY: Download the most recent database backup.
    """
    require_dev()
    loader = aws.DatabaseBackup(env.app.name)
    loader.download()


@task
def db_load_script(script_name):
    db = env.db_admin
    run('pwd')
    db.load_script(join(env.appdir, script_name))


def require_dev():
    """
    Exit if this is not a development environment.
    """
    if not env.server.is_dev:
        sys.exit("Don't do this on live.")


def require_live():
    """
    Exit if this is not a live production environment.
    """
    if env.server.is_dev:
        fatal("Don't do this on dev.")


@task
def load_dev_fixtures(db=None):
    """
    DEV ONLY: Load database fixtures for development environments.
    """
    require_dev()
    if db is None:
        db = env.db_admin  # type: MySQL

    db.query("UPDATE WWW_Users SET Theme = 'claro', xmpp = ''")
    db.query("DELETE FROM Shopify_Storefront")
    db.query("DELETE FROM Magento2_Storefront")
    db.insert_row("Magento2_Storefront", dict(
        storeUrl=env.server.make_hostname('store'),
        apiKey='rialto-api-key',
        userID='magento',
        salesTypeID='OS',
        quoteTypeID='DI',
        salesmanID='OSC',
        stockLocationID='7'))
    db.query("UPDATE Printer SET host = 'localhost', port = 9100")
    for uuid, user_id in env.uuids.items():
        sql = "REPLACE INTO Security_SsoLink (uuid, userID) VALUES ('{}', '{}')"
        db.query(sql.format(uuid, user_id))


@task
def user_uuid(username, uuid):
    """
    Set the UUID of an existing user.
    """
    env.console.run('sso:uuid {} {}'.format(username, uuid))


@task
def user_create(username, uuid):
    """
    Create a new admin user with the given username and UUID.
    """
    env.console.run('user:create {} {}'.format(username, uuid))


@task
def local_databases():
    """
    Install database servers for local dev instances.
    """
    require_dev()
    apt = Apt()
    apt.install(['mysql-server', 'mongodb'], recommends=False)


@task
def crontab(on_dev='n'):
    """
    Install Rialto's crontab.

    @param on_dev: whether to install the crontab on dev environments
                   (default no)
    """
    if env.server.is_live or on_dev == 'y':
        install_crontab('crontab.template', 'rialto', {
            'appdir': env.appdir,
            'env': env.app_env,
        })


@task
def crontab_remove():
    """
    Remove the Rialto crontab to disable any background jobs.
    """
    server.remove_crontab('rialto')


@task
def crontab_view():
    """
    View the active crontabs.
    """
    sudo('cat /etc/cron.d/*')


@task
def supervisor():
    """
    Configure supervisor for managing background daemon processes.
    """
    sudo('service supervisor stop', warn_only=True)
    upload_template(
        filename='supervisor.conf.template',
        destination='/etc/supervisor/conf.d/rialto.conf',
        context={
            'appdir': env.appdir,
            'app_env': env.app_env,
            'logdir': env.logdir,
            'verbosity': '-vvv' if env.server.is_dev else '-vv',
        },
        use_sudo=True)
    execute(jobs_status, 2)


@task
def gitlog(num=5):
    """
    Show the most recent Git commits on the server.
    """
    env.git.log(num)


@task
def test_printer_connection(port='9100', host='10.0.1.112'):
    """
    Test the connection to the warehouse printer.
    """
    run('echo "Test" | nc -v {} {}'.format(host, port))


@task
def licenses():
    """
    Show the licenses of our dependencies.
    """
    print("\n=== PHP ===")
    local('composer licenses')
    print("\n=== Javascript ===")
    local('nlf -d --summary detail')


@task
def dump_table(table):
    """
    Dump the data from a database table to stdout.

    Useful for populating test fixtures.
    """
    cmd = 'mysqldump --compact --complete-insert --no-create-info {} {}'
    local(cmd.format(env.db_admin.name, table))


@task
def watch_logs():
    """
    Watch the error and accesss logs.
    """
    with cd(env.logdir):
        sudo('tail -f nginx/*.log /var/log/syslog /var/log/php*.log')


@task
def register():
    """
    Register this instance in the target group for the load balancer.
    """
    register_target(env.target_group, env.server.instance.id)


@task
def deregister():
    """
    Remove this instance as a target for the load balancer.
    """
    deregister_target(env.target_group, env.server.instance.id)


@task
def describe_targets():
    describe_target_group(env.target_group)


@task
def whats_next():
    """
    Instructions and tasks for finishing a server bringup.
    """
    if not env.server.is_remote:
        return

    def instr(msg):
        prompt(green("\n{}".format(msg)))
    print(green("Press ENTER after finishing each of the following steps:"))
    instr("`fab jobs_stop` on the old server.")
    instr("`fab register` on the new server.")
    instr("`fab deregister` on the old server.")
    instr("Check that the new server is up.")
    instr("`fab down` on the old server.")
    instr("Check that the new server is up.")
    instr("Stop the old EC2 instance.")
    instr("Check that the new server is up.")
    instr("Terminate the really old EC2 instance.")
    instr("`fab jobs_restart` on the new instance.")
    if env.server.is_live:
        execute(tag_release)
