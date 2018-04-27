namespace :php do

  desc '配置 Laravel 環境。'
  task :setup do
    on roles(:app) do
      within release_path  do
        execute :chmod, "u+x artisan"
        execute :chmod, "-R 777 storage/logs"
        execute :chmod, "-R 777 storage/framework"
        execute :chmod, "-R 777 bootstrap/cache"
      end
    end
  end

  desc '執行 Composer 更新。'
  after :setup, :composer do
    on roles(:app) do
      within release_path  do
        execute :php, "composer update"
      end
    end
  end

end
