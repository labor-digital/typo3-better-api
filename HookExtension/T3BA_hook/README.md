# T3BA Hook Adapter

This dummy extension is loaded dynamically (and invisible in the extension management) to make sure to include stuff
like the tca files, routes and middlewares at the latest possible moment, but before the TCA migrations are executed.

This is important because we want all other extensions to be able to do their stuff, before we do our stuff :)
