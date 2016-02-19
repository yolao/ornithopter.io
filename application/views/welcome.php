<h2>Routing Basics</h2>
<p>
  Ornithopter routes to the <em>home.php</em> controller using the <em>index</em> action by
  default. Since this is a GET request, routing called the <strong>get_index()</strong> method
  within the <strong>home</strong> controller class. These demo pages are provided to help you grasp
  routing, and introduce you to using the features built into the framework. Here's what happened;
</p>
<blockquote>
  <h3>Request :: Explaination</h3>
  <section>
    <p>
      <strong>Controller:</strong> application/home
    </p>
    <p>
      <strong>Method:</strong> get_index()
    </p>
    <p>
      <strong>Action:</strong> index
    </p>
  </section>
  <hr />
  <h3>Details :: Step-by-step Processing Guide</h3>
  <ol>
    <li>
      All requests go through <em>index.php</em> ...
    </li>
    <li>
      <em>index.php</em> included <em>application/ornithopter.php</em> and initialized the <strong class="io">io</strong> class for use.
    </li>
    <li>
      The <strong class="io">helpers</strong> and <strong class="io">libraries</strong> along with <strong class="io">io::route()</strong> and <strong class="io">io::info()</strong> methods are initialized.
    </li>
    <li>
      The controller <em>application/home.php</em> is loaded, since "home" is the default route.
    </li>
    <li>
      The methods <strong class="action">before_index()</strong>, <strong class="action">get_index()</strong> and <strong class="action">after_index()</strong> are called in that order.
    </li>
  </ol>
  <small>Ornithopter.io routes to controllers based on the URL of your website. The default controller is always <strong>home</strong> and the default action is always <strong>index</strong> ... Even for subfolders. It's important to familiarize yourself with Ornithopter.io routing.</small>
</blockquote>
<h2>Routing In-Depth Explaination</h2>
<p>
  Routing is automatic. Think of your controllers as the main interface between your app
  and user requests. They capture requests based on the protocol used, controller being called, and
  finally the action specified. It might be helpful to call <strong class="io">io::route()</strong> to see how
  Ornithopter is routing a specific request. Controllers specify protocol relevant methods (actions)
  which then perform some sort of processing for that <strong class="action">protocal_action()</strong> within the controller.
</p>
<blockquote>
  <h3><strong>Clean URLs</strong> :: Routing with .htaccess (or <strong>mod_rewrite enabled</strong>)</h3>
  <hr />
  <small>[Example A]</small> <em>http://localhost/</em> <strong class="controller">controller</strong> / <strong class="action">action</strong> / <em>param1</em> / <em>param2</em> / <em>{...}</em>
  <ul>
    <li>
      <em>http://localhost/</em> <strong class="controller">home</strong> / <strong class="action">index</strong> / &nbsp; <strong>=></strong> &nbsp; <strong class="controller">home</strong> (controller) | <strong class="action">index</strong> (action)
      <ul>
        <li>
          <small>Methods called in <strong class="controller">application/controller/home.php</strong>:</small>
          <br />
          <small>[1] <strong class="action">before_index()</strong>, [2] <strong class="action">get_index()</strong> and [2] <strong class="action">after_index()</strong> ... (in this order)</small>
        </li>
      </ul>
    </li>
  </ul>
  <hr />
  <small>[Example B]</small> <em>http://localhost/</em> <strong class="controller">controller</strong> / <strong class="action">action</strong> / <em>param1</em> / <em>param2</em> / <em>{...}</em>
  <ul>
    <li>
      <em>http://localhost/</em> <strong class="controller">user</strong> / <strong class="action">profile</strong> / &nbsp; <strong>=></strong> &nbsp; <strong class="controller">user</strong> (controller) | <strong class="action">profile</strong> (action)
      <ul>
        <li>
          <small>Methods called in <strong class="controller">application/controller/user.php</strong>:</small>
          <br />
          <small>[1] <strong class="action">before_profile()</strong>, [2] <strong class="action">get_profile()</strong> and [2] <strong class="action">after_profile()</strong> ... (in this order)</small>
        </li>
      </ul>
    </li>
  </ul>
</blockquote>
<blockquote>
  <h3><strong>Ugly URLs</strong> :: Routing without .htaccess (or <strong>mod_rewrite disabled</strong>)</h3>
  <hr />
  <em>http://localhost/<strong class="ugly">index.php</strong>/</em> <strong class="controller">controller</strong> / <strong class="action">action</strong> / <em>param1</em> / <em>param2</em> / <em>{...}</em>
  <ul>
    <li>
      <em>http://localhost/<strong class="ugly">index.php</strong>/</em> <strong class="controller">home</strong> / <strong class="action">index</strong> / &nbsp; <strong>=></strong> &nbsp; <strong class="controller">home</strong> (controller) | <strong class="action">index</strong> (action)
      <ul>
        <li>
          <small>Methods called in <strong class="controller">application/controller/home.php</strong>:</small>
          <br /><hr />
          [1] <strong class="action">before_index()</strong>, [2] <strong class="action">get_index()</strong> and [2] <strong class="action">after_index()</strong> ... (in this order)
        </li>
      </ul>
    </li>
  </ul>
</blockquote>
<h2>Routing Hints</h2>
<p>
  Specifying <strong class="action">before_action()</strong> and <strong class="action">after_action()</strong> methods is completely optional. This may be useful for psuedo constructors and destructors, or code you want to run before and after particular actions. If you don't want to run code before or after an action, don't specify the methods. Ornithopter will still run just fine either way.
</p>
<p>
  You can use other types of REQUEST_METHOD actions in your controllers. Since standard HTTP requests are GET requests by default we use <strong class="action">get_action()</strong> for displaying web pages. However if a user (for example) submits a form using a POST method, you can handle this request with a <strong class="action">post_action()</strong> method in your controller...
</p>
<p>
  Take a look at the examples provided to get a better understanding of how this all works.
</p>
<h2>Generated by before_index() ...</h2>
