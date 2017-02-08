![FiercePhish](http://i.imgur.com/5WyejWU.png)

# FiercePhish


FiercePhish is a full-fledged phishing framework to manage all phishing engagements.  It allows you to track separate phishing campaigns, schedule sending of emails, and much more. The features will continue to be expanded and will include website spoofing, click tracking, and extensive notification options.

**Note: As of 1/6/2017, FirePhish has been renamed FiercePhish. Screenshots may still show FirePhish logo**

# All Information is on the Wiki Pages

[ChangeLog](https://github.com/Raikia/FiercePhish/wiki/Changelog)

[Click here to go to the Wiki Pages](https://github.com/Raikia/FiercePhish/wiki)

# Disclaimer

This project is my own and is not a representation of my employer's views. It is my own side project and released by me alone.

# Screenshot

![Screenshot](http://i.imgur.com/v852BbM.png)

More screenshots are available in the ["Features" wiki pages](https://github.com/Raikia/FiercePhish/wiki/Features-Overview)

# Quick Automated Install

For more information (like a manual installation method), see the [wiki pages](https://github.com/Raikia/FiercePhish/wiki)

This is the preferred method of installing FiercePhish + SMTP + IMAP services.

### Supported Operating Systems
* Ubuntu 16.04
* Ubuntu 16.10

(Fresh installs are expected, but the installer should work on a used OS with no problems)

(Ubuntu 14.04 support has been removed. To install FiercePhish on 14.04, [read these instructions](https://github.com/Raikia/FiercePhish/wiki/Ubuntu-14.04-Installation-Guide))

_If you would like a different OS distribution supported, create a [Github issue](https://github.com/Raikia/FiercePhish/issues)_


### Recommended Prerequisites
* Purchase a domain name to send emails from

This isn't required, but it is heavily suggested. Phishing campaigns where you spoof an active domain you don't own are extremely susceptible to being spam filtered (unless the domain's SPF record is improperly configured). The best way to perform a phishing campaign is by buying a generic domain that can fool someone ("yourfilehost.com") or a domain that is very similar to a real domain ("microsoft-secure.com").

### Installation Method #1 (remote curl download)

This method is probably the easiest way to install/configure everything. It is a fully unattended installation (aside from the beginning).

 1. You must run the installer as root:

   ```sudo su```

 2. Generate the configuration file:

   ```curl https://raw.githubusercontent.com/Raikia/FiercePhish/master/install.sh | bash```

 3. This will create a configuration file located at "~/fiercephish.config".  You must edit this file before moving on!

   [Click here for a detailed description of the configuration variables](https://github.com/Raikia/FiercePhish/wiki/Installation-Configuration-File)

 4. Once "CONFIGURED=true" is set in the configuration file, re-run the install script:

   ```curl https://raw.githubusercontent.com/Raikia/FiercePhish/master/install.sh | bash```

 5. Sit and wait.  The installation could take anywhere from 5-15 minutes depending on your server's download speed.

 6. Once the installation completes, follow the instructions it prints out.  It will tell you what [DNS entries](https://github.com/Raikia/FiercePhish/wiki/DNS-Configurations) to set.


### Installation Method #2 (local installation run)

This method is just as easy as method #1, but the install will prompt you as it runs for the information it requires (as opposed to using a configuration file like method #1).

 1. You must run the installer as root:

   ```sudo su```
 
 2. Download the configuration file:

   ```wget https://raw.githubusercontent.com/Raikia/FiercePhish/master/install.sh```

 3. Set the installer as executable:

   ```chmod +x install.sh```

 4. Run the installer:

   ``` ./install.sh ```

   The installer will prompt you for the same information as is described in [the configuration file for method #1](https://github.com/Raikia/FiercePhish/wiki/Installation-Configuration-File).  See that wiki page for information on what to provide.

 5. Sit and wait.  The installation could take anywhere from 5-15 minutes depending on your server's download speed.

 6. Once the installation completes, follow the instructions it prints out.  It will tell you what [DNS entries](https://github.com/Raikia/FiercePhish/wiki/DNS-Configurations) to set.


### Updating

As of FiercePhish v1.2.0, an update script is included.  Versions prior to 1.2.0 are **not** compatible with 1.2.0 and later, so you'll have to do a fresh install (or read the wiki).

To update FiercePhish, simply run:
   ```
    sudo ./update.sh
   ```
### Troubleshooting

If you have errors with the installation script, you can safely rerun the script without messing anything up (even if you provide it different information). If you continue to have problems, set "VERBOSE=true" (for method #1) or run ```./install.sh -v``` (for method #2) to see the full log of everything running.  If you still have problems, [submit a bug report](https://github.com/Raikia/FiercePhish/wiki/Reporting-Bugs).