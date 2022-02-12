#!/bin/bash
CURRENT_PATH="$(pwd)"
BASEDIR=$(dirname "$0")
BASEDIR=$(realpath $BASEDIR)

echo "🛠️ Loading functions";
. "$BASEDIR/include/functions.sh"


# =======================================================================================================
# =======================================================================================================

# configuration checking
if [ ! -f "$BASEDIR/_configuration.sh" ]; then
    echo "🟥 You must create a _configuration.sh file."
    echo "Use _configuration.sample.sh for exemple";
    exit;
fi

echo "🛠️ Loading configuration";
. "$BASEDIR/_configuration.sh";

# =======================================================================================================

if [ -z $1 ]; then
    read -p "Installation folder ? " INSTALL_PATH;
    INSTALL_PATH=$(realpath $CURRENT_PATH"/"$INSTALL_PATH);
else
    INSTALL_PATH=$(realpath $CURRENT_PATH"/"$1);
fi

# =======================================================================================================

wpi_display_install_info;


read -p "Continue ? [y]/n : " CONFIRM_INSTALL;
if [ "$CONFIRM_INSTALL" = "n" ]; then
    echo "❌ Aborting installation";
    exit;
else
    echo "✔️ Starting installation";
fi

echo;


wpi_test_parent_url $WORDPRESS_URL;
wpi_handle_createfolder;
wpi_test_url $WORDPRESS_URL;


wpi_bdd_setup;
wpi_handle_wpcli;


wpi_handle_gitignore;

wpi_handle_composer;

wpi_handle_index;
wpi_handle_wpconfig;
wpi_wp_cli_config;



wpi_handle_wpinstall;
wpi_handle_postinstall;


echo "👌 Install successfull.";
echo "      🏠 Front URL : $WORDPRESS_URL";
echo "      ⚙️ Back URL : $WORDPRESS_URL/$WORDPRESS_FOLDER/wp-admin";


cd $CURRENT_PATH;