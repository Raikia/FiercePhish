![FirePhish](https://camo.githubusercontent.com/b14281b4d64b34eaad19062e26264fbdc95ce4ff/687474703a2f2f692e696d6775722e636f6d2f4d6f76374b52672e706e67)

# FirePhish


FirePhish is a full-fledged phishing framework to manage all phishing engagements.  It allows you to track separate phishing campaigns, schedule sending of emails, and much more. The features will continue to be expanded and will include website spoofing, click tracking, and extensive notification options.

# All Information is on the Wiki Pages

[Click here to go to the Wiki Pages](https://github.com/Raikia/FirePhish/wiki)


# Screenshot

![Screenshot](http://i.imgur.com/v852BbM.png)

More screenshots are available in the ["Features" wiki pages](https://github.com/Raikia/FirePhish/wiki/Features-Overview)

# Quick Automated Install

For more information (like a manual installation method), see the [wiki pages](https://github.com/Raikia/FirePhish/wiki)

This is the preferred method of installing FirePhish + SMTP + IMAP services.

### Supported Operating Systems
* Ubuntu 14.04
* Ubuntu 16.04
* Ubuntu 16.10

(Fresh installs are expected, but the installer should work on a used OS with no problems)

_If you would like a different OS distribution supported, create a [Github issue](https://github.com/Raikia/FirePhish/issues)_


### Recommended Prerequisites
* Purchase a domain name to send emails from

This isn't required, but it is heavily suggested. Phishing campaigns where you spoof an active domain you don't own are extremely susceptible to being spam filtered (unless the domain's SPF record is improperly configured). The best way to perform a phishing campaign is by buying a generic domain that can fool someone ("yourfilehost.com") or a domain that is very similar to a real domain ("microsoft-secure.com").

### Installation Method #1 (remote curl download)

This method is probably the easiest way to install/configure everything. It is a fully unattended installation (aside from the beginning).

 1. You must run the installer as root:

   ```sudo su```

 2. Generate the configuration file:

   ```curl https://raw.githubusercontent.com/Raikia/FirePhish/master/installer.sh | bash```

 3. This will create a configuration file located at "~/firephish.config".  You must edit this file before moving on!

   [Click here for a detailed description of the configuration variables](https://github.com/Raikia/FirePhish/wiki/Installation-Configuration-File)

 4. Once "CONFIGURED=true" is set in the configuration file, re-run the install script:

   ```curl https://raw.githubusercontent.com/Raikia/FirePhish/master/installer.sh | bash```

 5. Sit and wait.  The installation could take anywhere from 5-15 minutes depending on your server's download speed.

 6. Once the installation completes, follow the instructions it prints out.  It will tell you what [DNS entries](https://github.com/Raikia/FirePhish/wiki/DNS-Configurations) to set.


### Installation Method #2 (local installation run)

This method is just as easy as method #1, but the install will prompt you as it runs for the information it requires (as opposed to using a configuration file like method #1).

 1. You must run the installer as root:

   ```sudo su```
 
 2. Download the configuration file:

   ```wget https://github.com/Raikia/FirePhish/blob/master/installer.sh```

 3. Set the installer as executable:

   ```chmod +x installer.sh```

 4. Run the installer:

   ``` ./installer.sh ```

   The installer will prompt you for the same information as is described in [the configuration file for method #1](https://github.com/Raikia/FirePhish/wiki/Installation-Configuration-File).  See that wiki page for information on what to provide.

 5. Sit and wait.  The installation could take anywhere from 5-15 minutes depending on your server's download speed. *It will prompt you periodically, so make sure you pay attention. If you want to hit install and go afk, use method #1*

 6. Once the installation completes, follow the instructions it prints out.  It will tell you what [DNS entries](https://github.com/Raikia/FirePhish/wiki/DNS-Configurations) to set.



### Troubleshooting

If you have errors with the installation script, you can safely rerun the script without messing anything up (even if you provide it different information). If you continue to have problems, set "VERBOSE=true" (for method #1) or run ```./installer.sh -v``` (for method #2) to see the full log of everything running.  If you still have problems, [submit a bug report](https://github.com/Raikia/FirePhish/wiki/Reporting-Bugs).