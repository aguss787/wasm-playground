# wasm-playground

Experimental to test wasm integration with various languages

## How to deploy

### Requirements

- Ansible

### Steps

1. `cd playgrounds`
2. Copy `env.example` to `env`
3. Generate a public/private key pair, called `ansible` and `ansible.pub` in `env/ssh_keys`
4. Put your public key in `env/ssh_keys`
5. Change `env/vars.yml` according to your needs
6. Change `env/hosts.ini` ip addresses with your server(s)
7. Provision your machine by using this command: `ANSIBLE_HOST_KEY_CHECKING=False ansible-playbook -i env/hosts.ini -e @env/vars.yml -e ansible_ssh_private_key_file= --ssh-extra-args="-o UpdateHostKeys=yes -o StrictHostKeyChecking=accept-new" provision/ubuntu/main.yml`
8. Deploy php server by using this command: `ansible-playbook -i env/hosts.ini -e @env/vars.yml deploy_php.yml`
