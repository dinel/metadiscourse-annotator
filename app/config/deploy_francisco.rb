set :application, "Metadiscourse annotator"
set :domain,      "www.dinel.org.uk"
ssh_options[:port] = "2223"
set :deploy_to,   "/home2/dinel/subdomains/meta-francisco"
set :app_path,    "app"

set :repository,  "https://github.com/dinel/metadiscourse-annotator.git"
set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain                         # Your HTTP server, Apache/etc
role :app,        domain, :primary => true       # This may be the same as your `Web` server

set  :keep_releases,  3

set :dump_assetic_assets, true
set :use_composer, true
set :update_vendors, true

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,   [app_path + "/logs", web_path + "/uploads", "vendor", app_path + "/sessions"]

set :use_sudo,      false
set :user, "dinel"

set :writable_dirs,       ["app/cache", "app/logs", "app/sessions"]
set :webserver_user,      "dinel"
set :permission_method,   :acl
set :use_set_permissions, true
set :group_writable, false

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL