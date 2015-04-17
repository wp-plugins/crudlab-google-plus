<?php
if (!defined('ABSPATH'))
    exit;

class CLGPSettings {

    /**
     *
     * @var CLGooglePlusBtn
     */
    private $parrent = null;

    public function __construct(CLGooglePlusBtn &$parrent) {
        $this->parrent = $parrent;
        add_action('wp_ajax_getpostpages', array($this, 'getExcludesPages'));
        add_action('wp_ajax_clgpactive', array($this, 'activePlugin'));
    }

    public function getExcludesPages() {
        $data = array();
        $args = array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        $posts = get_posts($args);
        foreach ($posts as $post) {
            $data[] = array('id' => $post->ID, 'name' => $post->post_title);
        }

        echo json_encode($data);
        wp_die();
    }

    public function activePlugin() {
        global $wpdb;
        $wpdb->update($this->parrent->getTable_name(), array('status' => (isset($_REQUEST['status']) && $_REQUEST['status'] == 'true') ? 1 : 0), array('id' => 1));
        $this->parrent->reloadDBData();

        echo json_encode(array('status' => $this->parrent->getSettingsData()->status));
        wp_die();
    }

    public function addJSCSS() {
        add_action('wp_enqueue_scripts', array($this, 'registerJSCSS'));
    }

    private function getAnnotation() {
        return array(
            'inline' => 'inline',
            'bubble' => 'bubble',
            'none' => 'none'
        );
    }

    private function getLang() {
        return array(
            'Afrikaans' => 'af',
            'Amharic' => 'am',
            'Arabic' => 'ar',
            'Basque' => 'eu',
            'Bengali' => 'bn',
            'Bulgarian' => 'bg',
            'Catalan' => 'ca',
            'Chinese (Hong Kong)' => 'zh-HK',
            'Chinese (Simplified)' => 'zh-CN',
            'Chinese (Traditional)' => 'zh-TW',
            'Croatian' => 'hr',
            'Czech' => 'cs',
            'Danish' => 'da',
            'Dutch' => 'nl',
            'English (UK)' => 'en-GB',
            'English (US)' => 'en-US',
            'Estonian' => 'et',
            'Filipino' => 'fil',
            'Finnish' => 'fi',
            'French' => 'fr',
            'French (Canadian)' => 'fr-CA',
            'Galician' => 'gl',
            'German' => 'de',
            'Greek' => 'el',
            'Gujarati' => 'gu',
            'Hebrew' => 'iw',
            'Hindi' => 'hi',
            'Hungarian' => 'hu',
            'Icelandic' => 'is',
            'Indonesian' => 'id',
            'Italian' => 'it',
            'Japanese' => 'ja',
            'Kannada' => 'kn',
            'Korean' => 'ko',
            'Latvian' => 'lv',
            'Lithuanian' => 'lt',
            'Malay' => 'ms',
            'Malayalam' => 'ml',
            'Marathi' => 'mr',
            'Norwegian' => 'no',
            'Persian' => 'fa',
            'Polish' => 'pl',
            'Portuguese (Brazil)' => 'pt-BR',
            'Portuguese (Portugal)' => 'pt-PT',
            'Romanian' => 'ro',
            'Russian' => 'ru',
            'Serbian' => 'sr',
            'Slovak' => 'sk',
            'Slovenian' => 'sl',
            'Spanish' => 'es',
            'Spanish (Latin America)' => 'es-419',
            'Swahili' => 'sw',
            'Swedish' => 'sv',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Thai' => 'th',
            'Turkish' => 'tr',
            'Ukrainian' => 'uk',
            'Urdu' => 'ur',
            'Vietnamese' => 'vi',
            'Zulu' => 'zu'
        );
    }

    public function registerJSCSS() {

        wp_register_style('clgp-style-flat', plugins_url('dist/css/flat-ui.css', __FILE__), array(), '20120208', 'all');
        wp_register_style('clgp-style', plugins_url('dist/css/style.css', __FILE__), array(), '20120208', 'all');
//        wp_register_style('clgp-style-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/css/select2.min.css', array(), '20120208', 'all');
        wp_register_style('clgp_magicsuggest-min', plugins_url('dist/css/magicsuggest-min.css', __FILE__));

        wp_enqueue_style('clgp-style-flat');
        wp_enqueue_style('clgp_magicsuggest-min');
        wp_enqueue_style('clgp-style');
//        wp_enqueue_style('clgp-style-select2');

        wp_register_script('clgp-src-vid', plugins_url('dist/js/vendor/video.js', __FILE__), array('jquery'));
        wp_register_script('clgp-src-falt', plugins_url('dist/js/flat-ui.min.js', __FILE__), array('jquery'));
//        wp_register_script('clgp-scr-app', plugins_url('docs/assets/js/application.js', __FILE__), array('jquery'));
//        wp_register_script('clgp-scr-select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0-rc.2/js/select2.min.js', array('clgp-scr-app'));
        wp_register_script('clgp_magicsuggest', plugins_url('js/magicsuggest-min.js', __FILE__), array('jquery'));
        wp_register_script('clgp-srcipt', plugins_url('js/scripts.js', __FILE__), array('jquery'));
        wp_register_script('clgp-radiocheck', plugins_url('js/radiocheck.js', __FILE__), array('jquery'));

        wp_enqueue_script('clgp-scr-vid');
        wp_enqueue_script('clgp-src-falt');
        wp_enqueue_script('clgp_magicsuggest');
//        wp_enqueue_script('clgp-scr-app');
//        wp_enqueue_script('clgp-scr-select2');
        wp_enqueue_script('clgp-srcipt');
        wp_enqueue_script('clgp-radiocheck');
        wp_enqueue_script('jquery-ui-tooltip');
    }

    public function validateData() {
        return true;
    }

    public function saveData() {
        $display_val = 0;
        if (isset($_POST['display']) && is_array($_POST['display'])) {
            $display = $_POST['display'];
            foreach ($display as $d) {
                $display_val += intval($d);
            }
        }

        $except_ids = isset($_POST['except_ids']) ? $_POST['except_ids'] : null;
        if ($except_ids != NULL) {
            $except_ids = implode(',', $except_ids);
        }
        $data1 = array(
            'display' => $display_val,
            'except_ids' => $except_ids,
            'share_type' => $_POST['share_type'],
            'share_type_url' => $_POST['share_type_url'],
            'beforeafter' => $_POST['beforeafter'],
            'position' => $_POST['position'],
            'language' => $_POST['language'],
            'size' => $_POST['size'],
            'annotation' => $_POST['annotation'],
            'width' => intval($_POST['width']) ? intval($_POST['width']) : null,
        );
        global $wpdb;
        $wpdb->update($this->parrent->getTable_name(), $data1, array('id' => 1));
        $this->parrent->reloadDBData();
    }

    public function renderPage() {
        $obj = $this->parrent->getSettingsData();
        echo '<script src="https://apis.google.com/js/platform.js" async defer>{lang: \'' . $obj->language . '\'}</script>';
        ?>
        <div class="plugins-wrap">
            <div class="col-left">
                <form method="post">
                    <div class="row">
                        <h4>CRUDLab Google Plus Settings</h4>
                        <div class="small">
                            Let visitors recommend your content on Google Search and share it on Google Plus
                        </div>
                    </div>
                    <div class="row">
                        <div class="where pull-left">
                            <div class="small ">
                                <strong>Where to Display?</strong>
                            </div>
                        </div>
                        <div class="options-wrap pull-left">
                            <div class="block">
                                <label class="checkbox" for="checkbox1">
                                    <input type="checkbox" name="display[]" <?php echo ($obj->display & 1) ? 'checked' : ''; ?> value="1" id="checkbox1" data-toggle="checkbox">
                                    Homepage
                                </label>
                            </div>
                            <div class="block">
                                <label class="checkbox" for="checkbox2">
                                    <input type="checkbox" name="display[]" <?php echo ($obj->display & 2) ? 'checked' : ''; ?> value="2" id="checkbox2" data-toggle="checkbox">
                                    All pages
                                </label>
                            </div>
                            <div class="block">
                                <label class="checkbox" for="checkbox3">
                                    <input type="checkbox" name="display[]" <?php echo ($obj->display & 4) ? 'checked' : ''; ?> value="4" id="checkbox3" data-toggle="checkbox">
                                    All posts
                                </label>
                            </div>
                            <div class="block">
                                <div class="block">
                                    <label class="checkbox" for="checkbox4">
                                        <input type="checkbox" onchange="((this.checked) ? jQuery('.clgp_exclude').show(200) : jQuery('.clgp_exclude').hide(200))" name="display[]" <?php echo ($obj->display & 8) ? 'checked' : ''; ?> value="8" id="checkbox4" data-toggle="checkbox">
                                        Exclude the following posts and pages
                                    </label>
                                </div>
                                <div class="block clgp_exclude" style="<?php echo ($obj->display & 8) ? 'display:block' : 'display:none'; ?>">
                                    <div id="magicsuggest" value="[<?php echo $obj->except_ids; ?>]" name="except_ids[]" style="width:auto !important; background: #fff; border: thin solid #cccccc;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="row">
                        <div class="block">
                            You can use <span class="inline-block shortcode" ><input value="[clgplus]" onClick="this.setSelectionRange(0, this.value.length);" /></span> shortcode to display gplus button
                        </div>
                        <div class="block">
                            <p>Also you can copy and paste the code in the theme files where you want</p>
                            <p><span class="inline-block shortcode" ><input value="&#60;&#63;php clgplusButton() &#63;&#62;" onClick="this.setSelectionRange(0, this.value.length);"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="where pull-left">
                            <div class="small ">
                                <strong>Share URL</strong>
                            </div>
                        </div>
                        <div class="options-wrap pull-left">
                            <div class="block">
                                <label class="radio">
                                    <input type="radio" name="share_type" <?php echo ($obj->share_type == 1) ? 'checked' : ''; ?> value="1" data-toggle="radio">
                                    Use the Page/Post URL
                                </label>
                            </div>
                            <div class="block">
                                <label class="radio">
                                    <input type="radio" name="share_type" <?php echo ($obj->share_type == 2) ? 'checked' : ''; ?> value="2" data-toggle="radio">
                                    Site URL
                                </label>
                            </div>
                            <div class="block">
                                <label class="radio">
                                    <input type="radio" name="share_type"  <?php echo ($obj->share_type == 3) ? 'checked' : ''; ?> value="3" data-toggle="radio"/>
                                    
                                </label>
                                <input type="text" class="form-control" value="<?php echo $obj->share_type_url ?>" placeholder="URL to 1+" name="share_type_url" />
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="row">
                        <div class="block">

                            <div class="block">
                                <div class="where pull-left">
                                    <div class="small ">
                                        <strong>&nbsp;</strong>
                                    </div>
                                </div>
                                <div class="options-wrap pull-left">
                                    <label class="radio">
                                        <input type="radio" name="beforeafter" <?php echo ($obj->beforeafter == 'after') ? 'checked' : ''; ?> value="after" data-toggle="radio">
                                        After
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="beforeafter" <?php echo ($obj->beforeafter == 'before') ? 'checked' : ''; ?> value="before" data-toggle="radio">
                                        Before
                                    </label>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="block">
                                <div class="where pull-left">
                                    <div class="small ">
                                        <strong>Position:</strong>
                                    </div>
                                </div>
                                <div class="options-wrap pull-left">
                                    <label class="radio">
                                        <input type="radio" name="position" <?php echo ($obj->position == 'left') ? 'checked' : ''; ?> value="left" data-toggle="radio">
                                        Left
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="position" <?php echo ($obj->position == 'center') ? 'checked' : ''; ?> value="center" data-toggle="radio" >
                                        Middle
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="position" <?php echo ($obj->position == 'right') ? 'checked' : ''; ?> value="right" data-toggle="radio">
                                        Right
                                    </label>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="block">
                            <div class="where pull-left">
                                <div class="small ">
                                    <strong>Language:</strong>
                                </div>
                            </div>
                            <div class="options-wrap pull-left">
                                <div class="block">
                                    <select class="form-control select select-default" name="language" data-toggle="select">
                                        <?php foreach ($this->getLang() as $key => $value) { ?>
                                            <option <?php echo ($value == $obj->language) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="block">
                            <div class="where pull-left">
                                <div class="small ">
                                    <strong>Size:</strong>
                                </div>
                            </div>
                            <div class="options-wrap pull-left">
                                <label class="radio">
                                    <input type="radio" name="size" <?php echo ($obj->size == 'small') ? 'checked' : ''; ?> value="small" data-toggle="radio">
                                    Small
                                </label>
                                <label class="radio">
                                    <input type="radio" name="size" <?php echo ($obj->size == 'medium') ? 'checked' : ''; ?> value="medium" data-toggle="radio" >
                                    Medium
                                </label>
                                <label class="radio">
                                    <input type="radio" name="size" <?php echo ($obj->size == 'standard') ? 'checked' : ''; ?> value="standard" data-toggle="radio">
                                    Standard
                                </label>
                                <label class="radio">
                                    <input type="radio" name="size" <?php echo ($obj->size == 'tail') ? 'checked' : ''; ?> value="tail" data-toggle="radio" >
                                    Tall
                                </label>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block">
                            <div class="where pull-left">
                                <div class="small ">
                                    <strong>Annotation:</strong>
                                </div>
                            </div>
                            <div class="options-wrap pull-left">
                                <div class="block">
                                    <select class="form-control select select-default" name="annotation" data-toggle="select">
                                        <?php foreach ($this->getAnnotation() as $key => $value) { ?>
                                            <option <?php echo ($value == $obj->annotation) ? 'selected="selected"' : ''; ?> value="<?php echo $value ?>"><?php echo $key; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="block">
                            <div class="where pull-left">
                                <div class="small ">
                                    <strong>Width:</strong>
                                </div>
                            </div>
                            <div class="options-wrap pull-left">
                                <div class="block">
                                    <input type="text" value="<?php echo ($obj->width) > 0 ? $obj->width : '' ?>" placeholder="120" name="width" class="form-control" />
                                    <span class="input-group-addon">px</span>
                                    <span id="fix" class="btn btn-success clgptooltip" data-original-title="Width is only for inline annotation. Please add at least 120px. If you don't know width just leave this field blank.">?</span>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="box previewgp" id="previewgp">

                        </div>

                        <div class="clearfix"></div>
                    </div>
                    <div class="row">
                        <button class="btn btn-block btn-lg btn-success">Save Settings</button>
                        <div class="pull-right">
                            <input type="checkbox" <?php echo ($obj->status) ? 'checked' : ''; ?> data-toggle="switch"  name="status" />
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
            <div class="col-right">
                <div class="sidebar-wrap">
                    <h2>   
                        <a href="http://crudlab.com" target="_blank">CRUDLab</a> has following plugins for you:
                    </h2>
                    <hr>
                    <div>
                        <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                            WP Like Button
                        </div>
                        <div style="margin-top:10px; margin-bottom: 8px;">
                            WP Like button allows you to add Facebook like button to your wordpress blog.
                        </div>
                        <div style="text-align: center;">
                            <a href="https://wordpress.org/plugins/wp-like-button/" target="_blank" class="wpfblbox_btn wpfblbox_btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                        </div>
                    </div>

                    <hr>
                    <div>
                        <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                            CRUDLab Facebook Like Box
                        </div>
                        <div style="margin-top:10px; margin-bottom: 8px;">
                            CRUDLab Facebook Like Box allows you to add Facebook like box to your wordpress blog. It allows webmasters to promote their Pages and embed a simple feed of content from a Page into their WordPress sites.
                        </div>
                        <div style="text-align: center;">
                            <a href="https://wordpress.org/plugins/crudlab-facebook-like-box/" target="_blank" class="btn-lg btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                            Jazz Popups
                        </div>
                        <div style="margin-top:10px; margin-bottom: 8px;">
                            Jazz Popups allow you to add special announcement, message or offers in form of text, image and video.
                        </div>
                        <div style="text-align: center;">
                            <a href="https://wordpress.org/plugins/jazz-popups/" target="_blank" class="btn-lg btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <div style="font-weight: bold;font-size: 20px; margin-top: 10px;">
                            WP Tweet Plus
                        </div>
                        <div style="margin-top:10px; margin-bottom: 8px;">
                            WP Tweet Plus allows you to add tweet button on your wordpress blog. You can add tweet Button homepage, specific pages and posts.
                        </div>
                        <div style="text-align: center;">
                            <a href="https://wordpress.org/plugins/wp-tweet-plus/" target="_blank" class="btn-lg btn-success" style="width:90%; margin-top:5px; margin-bottom: 5px; ">Download</a>
                        </div>
                    </div>
                </div>
                <div style="margin-top:15px;">
                    <span>
                        Your donation helps us make great products
                    </span>
                    <a href="https://www.2checkout.com/checkout/purchase?sid=102444448&quantity=1&product_id=1" target="_blank">
                        <img style="width:100%;" src="<?php echo plugins_url('/img/donate.png', __FILE__); ?>">
                    </a>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }

}
