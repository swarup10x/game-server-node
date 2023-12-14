for general linux server

create file
`bash
#!/bin/bash
sudo node release.js
`

run certbot. generate ssl

run
pm2 start start.sh --name release
