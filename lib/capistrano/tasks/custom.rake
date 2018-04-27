namespace :custom do

  desc '自訂的前置任務。'
  task :pre_setup do
    on roles(:all) do
      # do something before going to published
    end
  end

  desc '自訂的後置任務。'
  task :post_setup do
    on roles(:all) do
      execute :docker, "exec checkin composer install"
    end
  end

end
