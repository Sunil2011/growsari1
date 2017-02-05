#!/bin/bash

SRC_DIR=$(dirname $0)
source $SRC_DIR/utils.sh

VERSION=$1
APPLICATION_ENV=$2

#exit the script if the version is not defined
if [ -z "$VERSION" ]
  then
    echo "No Git version supplied! eg: deploy.sh master staging"
    exit 1
fi

#initialize the environment
if [ -z "$APPLICATION_ENV" ]; then
  APPLICATION_ENV=prod
elif [ "$APPLICATION_ENV" != "prod" ] && [ "$ENV" ]; then
  APPLICATION_ENV=$ENV
fi

echo "Using environment $APPLICATION_ENV"

if [ $APPLICATION_ENV == "prod" ]; then
  if [ "$VERSION" == "master" ]; then
    deploy_branch 
  elif [[ "$VERSION" == v* ]]; then
    deploy_tag
  else
    echo "Invalid version number. In production you can deploy a tag or master branch"
    exit
  fi

  python ./bin/build.py --env=prod

  chown -R ubuntu:www-data bin config data module vendor public
  chmod -R 755 bin config data module vendor public
  chmod -R 775 data/logs public/uploads data/super8 
  echo "Deploy finished"  
  
elif [ $APPLICATION_ENV == "staging" ]; then   
  deploy_branch

  python ./bin/build.py --env=$APPLICATION_ENV

  chown -R ubuntu:www-data *
  chmod -R 755 *
  chmod -R 777 data/logs  public/uploads data/super8
else
    echo "Environment is not allowed"
fi

echo "done!"
exit 0

