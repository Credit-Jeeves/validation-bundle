# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.require_version ">= 1.5.0"

if ! ENV["node_name"]
    puts "ERROR: you must set 'node_name' in your environment"
    exit 1
end
node_name = ENV["node_name"]
public_ip = ENV["public_ip"] ? ENV["public_ip"] : "192.168.56.56"

DB_ROOT_PASSWD = ENV["DB_ROOT_PASSWD"] ? ENV["DB_ROOT_PASSWD"] : "passw0rd"
DB_APP_PASSWD = ENV["DB_APP_PASSWD"] ? ENV["DB_APP_PASSWD"] : "passw0rd"

hostname = "#{node_name}.renttrack.com"
puts "RentTrack Development Environment at #{public_ip} as #{hostname}"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # All Vagrant configuration is done here. The most common configuration
  # options are documented and commented below. For a complete reference,
  # please see the online documentation at vagrantup.com.

  config.vm.hostname = hostname

  # Set the version of chef to install using the vagrant-omnibus plugin
  #config.omnibus.chef_version = :latest
  config.omnibus.chef_version = "11.16.4"

  # Every Vagrant virtual environment requires a box to build off of.
  # If this value is a shorthand to a box in Vagrant Cloud then
  # config.vm.box_url doesn't need to be specified.
  config.vm.box = "opscode_centos-6.5"

  # The url from where the 'config.vm.box' box will be fetched if it
  # is not a Vagrant Cloud box and if it doesn't already exist on the
  # user's system.
  config.vm.box_url = "http://opscode-vm-bento.s3.amazonaws.com/vagrant/virtualbox/opscode_centos-6.5_chef-provisionerless.box"

  # Assign this VM to a host-only network IP, allowing you to access it
  # via the IP. Host-only networks can talk to the host machine as well as
  # any other machines on the same network, but cannot be accessed (through this
  # network interface) by any external networks.
  #config.vm.network :private_network, type: "dhcp"
  config.vm.network "private_network", ip: public_ip

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "/Users/#{ENV['USER']}/renttrack/Credit-Jeeves-SF2", "/home/centos/dar.renttrack.com", type: "nfs"

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider :virtualbox do |vb|
    # Don't boot with headless mode
    vb.gui = true

    # Use VBoxManage to customize the VM. For example to change memory:
    vb.customize ["modifyvm", :id, "--memory", "4096"]
  end
  #
  # View the documentation for the provider you're using for more
  # information on available options.

  # The path to the Berksfile to use with Vagrant Berkshelf
  # config.berkshelf.berksfile_path = "./Berksfile"

  # Enabling the Berkshelf plugin. To enable this globally, add this configuration
  # option to your ~/.vagrant.d/Vagrantfile file
  # config.berkshelf.enabled = true

  # An array of symbols representing groups of cookbook described in the Vagrantfile
  # to exclusively install and copy to Vagrant's shelf.
  # config.berkshelf.only = []

  # An array of symbols representing groups of cookbook described in the Vagrantfile
  # to skip installing and copying to Vagrant's shelf.
  # config.berkshelf.except = []

  chef_attrs =
  {
     renttrack: {
       server_name: hostname,
       database: {
         name: 'renttrack',
         users: {
           root: {
             password: DB_ROOT_PASSWD,
             host: 'localhost',
             privileges: [:all]
           },
           renttrack: {
             password: DB_APP_PASSWD,
             host: 'localhost',
             privileges: [:create, :drop, :alter, :lock_tables, :references, :event, :delete, :index, :insert, :select, :update]
           }
         }
       }
     }
  }

  run_list = [
    "recipe[devops-collapsed::default]"
  ]

  #config.vm.provision :chef_solo do |chef|
  #  chef.json = chef_attrs
  #  chef.run_list = run_list
  #end

  config.vm.provision :chef_client do |chef|
     chef.chef_server_url = "https://api.opscode.com/organizations/renttrack"
     chef.validation_client_name = "renttrack-validator"
     chef.validation_key_path = "~/.chef/renttrack-validator.pem"
     chef.environment = "devops-stage"
     chef.json = chef_attrs
     chef.run_list = run_list
     chef.log_level = :info # use :debug for troubleshooting
  end
end
