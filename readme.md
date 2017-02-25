## Atlana
Atlana is a Content Management Framework (CMF) intended to be a one-size-fits-all framework via its modules. You can use Atlana with your existing libraries, preferably with Composer.

The biggest benefit with Atlana is the module management, which is very suitable for modular PHP applications. Imagine you have dozens of PHP classes, which are modules you want to integrate into your web application.

Instead for creating many classes manually, you give that task to Atlana.

*Example:*

**helloworld.php (modules/)**

    class helloworld extends module
    {
	    protected $message = null;
	    public function _init() {
		    $this->message = "Hello world!";
	    }
	    public function retvar() {
		    return $this->message;
	    }
    }
**your main application in index.php**

    require_once 'Atlana/Atlana.php';

    $atlana = new Atlana(false, 'default', false);
    // debug=false,cfg=default_cfg_path, sqluse=false
    $atlana->loadTemplates('atlana');
    //template loading from views/

    $atlana->tag(
	    'message_template',
	    $atlana->helloworld->retvar()
    );
    // get Atlana to load the helloworld module,
    // automatically for you!
    // tag() is for providing variables to Plates
    // or another template engine of your choice,
    // in this case it will get Plates to show 'Hello world'
    // to the visitor with the default template atlana

    echo $atlana->render('index'); 
  
## Installation
*Requirements:*
 - PHP 7.0+ (Atlana is in progress to move from PHP 5.3+)
 - Composer
 - APCU if you want cache
 - MySQL if you want SQL
 - Plates or Twig if you want templates (Twig support module is not yet uploaded)

Of course it's possible to make modules that provide support for other SQL, template solutions.

You will need to have a webroot structure like this, if you follow the default configuration (see Atlana.php class variables):

    [htdocs]
	    [Atlana]
		    [modules, models, sql folders]
		    - Atlana php files
		[yourwebsite]
	    	[conf] - folder for atlana.ini
	    	[views] - folder for your templates
	    	[public] - folder for your php files

In php.ini you should have include_path containing the path to "htdocs", so you can include 'Atlana/Atlana.php'. If you want to run a forum or similiar based on Atlana, you should have a MySQL installation done and you will need to adjust atlana.ini in that case.

See AtlanaCache.php for cache functions, powered by APCU.
