## OwOFrame
Open source license : ![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg) [Learn More](https://opensource.org/licenses/Apache-2.0)

OwO! `OwOFrame` is developed based on the `MVC (Model-Views-Controller)` model. The framework's standard formulation draws on `ThinkPHP`, so the naming rules for some methods seem There are similarities. This repository is just my personal practice repository.

If you think this repository is helpful to you, please give this repository a `Star` QWQ


[中文版简介请点这里](https://github.com/Tommy131/OwOFrame/blob/dev%40v1.0.2/README_CHS.md)
------

## What can I do?
> OwOFrame is a small framework that I developed using my free time. Of course, there are many shortcomings. The functions currently supported by this framework are listed below:

#### Basic System Components
- [x] `!!!IDE Friendly!!!`  All comments can be displayed well in `Visual Studio Code`, all are manual comments :)
- [x] `AppManager`          Judge the corresponding routing controller by identifying `HTTP_URI` and assign it to the corresponding Application
- [x] `CommandManager`      Support some operations through the Command Line method on the CLI
- [x] `ConfigurationParser` Configuration Parser
- [x] `EventManager`        Event Manager(Hooks Module)
- [x] `Exception`           Error capture and Stack output (I know that advanced frameworks have them and are better than mine QAQ)
- [x] `PluginLoader`        Plug-in loader (a support method that can be flexibly changed independent of Application)
- [x] `Language`            Custom languages packages supported
- [x] `Logger`              Support the most basic logging
- [x] `Redis`               Support basic Redis operations
- [x] `RouterManager`       Router Manager
- [ ] `BetterRouter`        Better Router (More personalized routing binding settings)
- [x] `Template`            Back-end rendering template (basic functions have been completed, turtle speed development advanced functions o(*￣▽￣*)o)
- [x] `TextFormat`          Support ANSI control code output color in CMD & Shell
- [x] `WMI`                 WMI operation class written for Windows system

#### Basic Util Components
- [x] `CookieClass`         A common Cookie class
- [x] `EmptyAppGenerator`   Generate a new Application template with one click
- [x] `FileUploadClass`     File upload support
- [x] `Helper`              An integrated method class (see the source code for details)
- [x] `SessionClass`        A common Session class

#### Third Party Resources
- [x] [`PasswordHash`](http://www.openwall.com/phpass/)
- [x] [`Think-ORM`](https://github.com/top-think/think-orm)


## How to use me?
### §1. Installation
1.1. First way can use command `git clone https://github.com/Tommy131/OwOFrame.git` to clone the repository from Github to location.<br/>
1.2. Or you can use the second way with command `composer create-project tommy131/owoframe -s dev` to create the repository to location.

### §2. Next Step
After open `CMD` in Windows or `Shell` in Linux, use command `cd owoframe && composer install` to change work path on owoframe.<br/>
If you used composer to install this repository, you may not to run the command from the top.


#### Do I need modify my Web configuration (e.g. for Nginx) ?
Yes. The step(s) please see the below:

```nginx
# Set your web root path to /public (Example);
root /www/owoframe/public;

location / {
    index index.php index.html;
    try_files $uri $uri/ /index.php$is_args$query_string;
}
```

#### How to running my Application in CLI-Mode with OwOFrame?
In the OwOFrame, I built the Command Manager. You can customize the registration management of the implementation Commands, and you can also modify the entry file in the CLI-Mode to touch your project requirements. The basically usage will be called in the root path like `owo [command]`.<br/>
Linux users need use command `./owo.sh [command]` to run the registed commands.

------

## Statement
&copy; 2016-2021 [`OwOBlog-DGMT`](https://www.owoblog.com). Please comply with the open source license of this project for modification, derivative or commercial use of this project.

> My Contacts:
- Website: [`HanskiJay`](https://www.owoblog.com)
- Telegram: [`HanskiJay`](https://t.me/HanskiJay)
- E-Mail: [`HanskiJay`](mailto:support@owoblog.com)


## Stargazers over time
[![Stargazers over time](https://starchart.cc/Tommy131/OwOFrame.svg)](https://starchart.cc/Tommy131/OwOFrame)
