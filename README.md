Welcome to Rialto
=================

Rialto is Gumstix' custom Enterprise Resource Planning (ERP) system. It is
responsible for automating all inventory, logistics, and accounting functions
of the company.

Installing Rialto
=================

-   Clone this repository to your system. (Make sure the URL below is
    up-to-date!)

        $ git clone git@bitbucket.org:gumstix/rialto.git

-   Rialto uses [Fabric][fabric] to automate the installation procedure, so install
    Fabric if you haven't already. For example:

        $ sudo apt-get install python-pip
        $ sudo pip install Fabric

-   Fabric works by SSHing into the machine it is going to set up; if you're
    installing Rialto locally, make sure you can SSH from your machine back
    into itself:

        $ ssh localhost

-   You also need to install [Fabtools][fabtools], which is our repository of shared
    custom libraries for Fabric. Many of our projects' fabfiles depend on
    Fabtools.
    
-   Create a copy of `rialto.ini.template` as `rialto.ini` and fill in any
    required configuration variables.

-   Run `fab -l` to see what tasks are available.

-   To set up a local instance, run `fab localhost setup`.

-   Get a database dump file from your manager and load it when `setup` prompts
    you to do so. If you skipped this step during setup, you can run it any
    time using the `db_load` task, like so:

        $ fab localhost db_load:path/to/rialto.dmp

-   After setup is complete, add `127.0.0.1  rialto.mystix.com` to `/etc/hosts`
    and access rialto.mystix.com in your browser to run Rialto locally.


[fabric]: http://www.fabfile.org/
[fabtools]: https://bitbucket.org/gumstix/fabtools
