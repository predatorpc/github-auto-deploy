__INSTALLATION__

1. Generate SSH keys for your www-data user on your VPS or Server or VM
> `$ sudo -u www-data ssh-keygen -t rsa`
2. Put keys into /home/www-data/.ssh folder and set correct rights
> `$ mkdir /home/www-data/`
> `$ mkdir /home/www-data/.ssh`
> `$ sudo mv KEYS_FILE_NAME_FROM_PREVIOUS_STEP /home/www-data/.ssh`
> `$ sudo chmod -R 700 /home/www-data/.ssh`
3. Check that user `www-data` is able to ru `git pull`, `git push` commands
> `$ sudo -u www-data git pull`
> '$ sudo -u www-data git push'
4. Copy files from project to any available from outside location i.e. 
   `http://site.com/update.php` for example.
5. Edit `update-config.php` file via copy from `update-config-sample.php`
   set your own values instead of templates XXX/ZZZ (configure!)
6. Go to `github.com` page over your project: Repository -> Settings -> Deploy Keys
   then add your public key, which generated in step 2.
7. Go to `github.com` page over your project: Repository -> Settings -> Webhooks
   then setup webhook address using step 4. data. Choose values and events you want
   to deploy on.
8. 

__PRINCIPLES__

+ Step 1. When you pushing in your repo from your IDE, or manually via CLI, this script
  looking for a newest version on your `master` branch and running `git pull`.
+ Step 2. If `git pull` succeeds we're running `tests` PHP Unit via codeception
+ Step 3. If testing succeeds we're writing to telegram full log and this is end.
+ Step 3.1 If testing fails script run `git reset --hard XXXXX` where XXXXX previous
  commit id. and then writing log to telegram

__PS__

Also: regular log is supported too, please configure it in `update-config.php`

__LICENSE__

The license is MIT. You're using it on your own risk :))