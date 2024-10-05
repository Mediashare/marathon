#!/bin/bash

detect_os() {
  case "$(uname -s)" in
      Linux*)   OS=linux;;
      Darwin*)  OS=osx;;
      CYGWIN*|MINGW*)  OS=win;;
      *)        OS=win;;
  esac

  echo $OS
}
current_directory() {
  CURRENT_DIRECTORY=$(pwd)

  if [ -z "$CURRENT_DIRECTORY" ]; then
      $CURRENT_DIRECTORY=$(cd)
  fi

  echo $CURRENT_DIRECTORY
}

install_binary() {
  VERSION=${1:-'main'}
  OS=${2:-$(detect_os)}
  DIRECTORY=${3-$(current_directory)}

  BASE_URL="https://raw.githubusercontent.com/Mediashare/marathon/$VERSION"

  case $OS in
      win*)   BINARY_NAME="marathon.exe";;
      *)      BINARY_NAME="marathon";;
  esac

  DOWNLOAD_URL="$BASE_URL/marathon"
  INSTALL_DIR="$DIRECTORY/$BINARY_NAME"

  curl -L -o $BINARY_NAME $DOWNLOAD_URL

  if [ $? -ne 0 ]; then
    echo "Download failed."
    exit 1
  fi

  mv $BINARY_NAME $INSTALL_DIR

  if [ $? -ne 0 ]; then
    sudo mv $BINARY_NAME $INSTALL_DIR
    if [ $? -ne 0 ]; then
      echo "Permission failed: you can retry with sudo."
      exit 1
    fi
  fi

  chmod +x $INSTALL_DIR

  echo "Installation successfull. The binary is installed into $INSTALL_DIR you can start managing your time on task with $BINARY_NAME command."
}

install_binary $1 $2 $3