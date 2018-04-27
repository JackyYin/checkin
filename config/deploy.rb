set :application, ENV['APPLICATION']
set :repo_url, ENV['REPO_URL']
set :deploy_to, ENV['DEPLOY_TO']
set :keep_releases, ENV['KEEP_RELEASES'].to_i

# Default branch is :master
ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp

# Default value for :pty is false
# set :pty, true

# Default value for default_env is {}
set :default_env, { path: "/usr/local/bin:$PATH" }

# Default value for local_user is ENV['USER']
# set :local_user, -> { `git config user.name`.chomp }

# set :use_sudo, true
# set :sudo, "sudo -u root -i"

append :linked_files, '.env'
append :linked_dirs, 'log', 'vendor', 'storage/logs', 'storage/framework', 'storage/debugbar'

### Custom your deploy flow
namespace :deploy do
  ### Reset releases directory's owner as deploy user
  # before 'deploy:cleanup', 'misc:reset_permission'

  ### Custom tasks before / after published
  # before 'deploy:published', 'custom:pre_setup'
  # after 'deploy:finished', 'custom:post_setup'

  ### Docker flow
  after 'deploy:published', 'docker:build'
  after 'docker:build', 'docker:compose'

  ### Yii (base on Docker) flow
  after 'docker:compose', 'custom:post_setup'

  ### Rollback flow
  # after :rollback, "docker:compose"
end
