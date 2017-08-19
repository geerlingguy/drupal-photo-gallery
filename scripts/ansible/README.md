# Ansible provisioning

To build a server for hosting the Drupal Photo Gallery site, an Ansible playbook is provided. This playbook will create a new [DigitalOcean](https://www.digitalocean.com/?refcode=b9c57af84643) droplet, install Docker on the droplet, then clone and install a copy of the Drupal Photo Gallery site.

## Dependencies

You need to install the following to use this playbook:

  - [Ansible](http://docs.ansible.com/ansible/latest/intro_installation.html)
  - [Dopy](https://pypi.python.org/pypi/dopy)

## Configure your site settings

If you'd like to override some of the default settings—for example, if you want to provide a custom `hostname`—create a `local.yml` file inside the `vars` folder (there's a `local.example.yml` file to get you started), then add any variable overrides.

Note that this playbook assumes you have an SSH key located somewhere on your computer (preferably in `~/.ssh/id_rsa.pub`, but you can set the `pubkey_location` to whatever you want). The playbook will make sure the public key is added to your DigitalOcean account.

## Set up DigitalOcean API access

Log into your DigitalOcean account (or [register for a new account](https://www.digitalocean.com/?refcode=b9c57af84643), then log in), and click on 'API'. On this page, click 'Generate New Token' to create a new API token.

Give the token a descriptive name (e.g. `drupal-photo-gallery`), then click 'Generate Token', making sure both Read and Write scopes are selected.

The token will be a very long string of gibberish—copy that text, and store it somewhere safe—you'll need it (or you'll need to create a new one) every time you interact with the DigitalOcean API!

## Provision your Droplet

  1. Open a Terminal and change directories to the `scripts/ansible` directory (the same directory as this README file).
  1. Add your DigitalOcean API access token to your current session: `export DO_API_TOKEN=your_token_goes_here`
  1. Run `ansible-galaxy install -r requirements.yml` to install required Ansible roles.
  1. Run `ansible-playbook digitalocean.yml` to provision the instance and setup Docker and the Drupal Photo Gallery site.

## Complete Gallery site setup

There are a few other things you need to do to complete the connection to your AWS account resources (set up via the CloudFormation template in the Rekognition API module):

  1. Log into your new site as the administrator.
  1. Visit the Configuration > Media > S3 File System page and add your AWS Access Key ID and Secret Key.
  1. Visit the Configuration > System > Basic site settings page and make sure the 'Default front page' is set to `/galleries`.
  1. Visit the Reports > Status Report page and verify there are no errors you need to resolve.

## Deploy changes to your Droplet

These playbooks are _idempotent_—meaning they should be able to be run once, or one thousand times, and in the end, the configuration will end up in the same state. So if you ever need to change settings, you should be able to change the variables, run the same `ansible-playbook` command again, and then see the changes on the server.

This playbook doesn't cover the full Drupal site lifecycle (e.g. database schema changes, cache clearing, etc.), so if you want to build a long-term fully-automated photo sharing platform... you'll need to do some more customization on your own!

Luckily, there's a book for that: [Ansible for DevOps](https://www.ansiblefordevops.com), a book about Ansible and automation (with many Drupal-specific examples!) written by this project's maintainer, [Jeff Geerling](https://www.jeffgeerling.com).
