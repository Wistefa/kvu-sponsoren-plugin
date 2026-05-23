<?php
/**
 * Plugin Name: KVU Sponsoren
 * Description: Sponsoren- und Partnerpagina des KV Untertürkheim. Shortcode: [kvu_sponsoren]
 * Version: 1.0.1
 * Author: KVU Untertuerkheim
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── AJAX: KONTAKTFORMULAR ────────────────────────────────────────────────────

add_action( 'wp_ajax_kvu_sponsoren_anfrage',        'kvu_sponsoren_anfrage' );
add_action( 'wp_ajax_nopriv_kvu_sponsoren_anfrage', 'kvu_sponsoren_anfrage' );

function kvu_sponsoren_anfrage() {
    if ( ! check_ajax_referer( 'kvu_sponsoren_nonce', 'nonce', false ) ) {
        wp_send_json_error( [ 'msg' => 'Ungültige Anfrage.' ] );
    }

    $firma   = sanitize_text_field( $_POST['firma']   ?? '' );
    $vorname = sanitize_text_field( $_POST['vorname'] ?? '' );
    $name    = sanitize_text_field( $_POST['name']    ?? '' );
    $email   = sanitize_email(      $_POST['email']   ?? '' );
    $paket   = sanitize_text_field( $_POST['paket']   ?? '' );
    $msg     = sanitize_textarea_field( $_POST['nachricht'] ?? '' );

    if ( ! $firma || ! $vorname || ! $name || ! is_email( $email ) ) {
        wp_send_json_error( [ 'msg' => 'Bitte alle Pflichtfelder ausfüllen.' ] );
    }

    $alle = array_filter( [
        get_option( 'kvu_sponsoren_email',  'info@kv-untertuerkheim.de' ),
        get_option( 'kvu_sponsoren_email2', '' ),
        get_option( 'kvu_sponsoren_email3', '' ),
    ] );
    $empfaenger = implode( ',', $alle );
    $betreff    = '[KVU Sponsoren] Anfrage von ' . $vorname . ' ' . $name . ' – ' . $firma;
    $inhalt     = "Neue Sponsor-Anfrage\n\n"
                . "Unternehmen: {$firma}\n"
                . "Name: {$vorname} {$name}\n"
                . "E-Mail: {$email}\n"
                . "Paket: {$paket}\n\n"
                . "Nachricht:\n{$msg}";

    $domain      = parse_url( home_url(), PHP_URL_HOST );
    $from_email  = 'wordpress@' . $domain;
    $from_name   = 'KVU Website';

    $from_filter = function() use ( $from_email ) { return $from_email; };
    $name_filter = function() use ( $from_name )  { return $from_name; };
    add_filter( 'wp_mail_from',      $from_filter );
    add_filter( 'wp_mail_from_name', $name_filter );
    $sent = wp_mail( $empfaenger, $betreff, $inhalt, [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email ] );
    remove_filter( 'wp_mail_from',      $from_filter );
    remove_filter( 'wp_mail_from_name', $name_filter );

    if ( $sent ) {
        wp_send_json_success( [ 'msg' => 'Vielen Dank! Wir melden uns in Kürze bei Ihnen.' ] );
    } else {
        wp_send_json_error( [ 'msg' => 'Fehler beim Senden. Bitte kontaktieren Sie uns direkt per E-Mail.' ] );
    }
}

// ── ADMIN-EINSTELLUNGEN ──────────────────────────────────────────────────────

add_action( 'admin_menu', 'kvu_sponsoren_admin_menu' );

function kvu_sponsoren_admin_menu() {
    add_menu_page(
        'KVU Sponsoren',
        'KVU Sponsoren',
        'manage_options',
        'kvu-sponsoren',
        'kvu_sponsoren_admin_page',
        'dashicons-awards',
        42
    );
}

function kvu_sponsoren_admin_page() {
    if ( isset( $_POST['kvu_sponsoren_save'] ) && check_admin_referer( 'kvu_sponsoren_settings' ) ) {
        update_option( 'kvu_sponsoren_email',  sanitize_email( $_POST['kvu_sponsoren_email']  ?? '' ) );
        update_option( 'kvu_sponsoren_email2', sanitize_email( $_POST['kvu_sponsoren_email2'] ?? '' ) );
        update_option( 'kvu_sponsoren_email3', sanitize_email( $_POST['kvu_sponsoren_email3'] ?? '' ) );
        echo '<div class="notice notice-success"><p>Einstellungen gespeichert.</p></div>';
    }
    $email  = get_option( 'kvu_sponsoren_email',  'info@kv-untertuerkheim.de' );
    $email2 = get_option( 'kvu_sponsoren_email2', '' );
    $email3 = get_option( 'kvu_sponsoren_email3', '' );
    ?>
    <div class="wrap">
        <h1>KVU Sponsoren — Einstellungen</h1>
        <form method="post">
            <?php wp_nonce_field( 'kvu_sponsoren_settings' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="kvu_sponsoren_email">Empfänger 1 *</label></th>
                    <td>
                        <input type="email" id="kvu_sponsoren_email" name="kvu_sponsoren_email"
                               value="<?php echo esc_attr( $email ); ?>" class="regular-text" required />
                        <p class="description">Pflichtfeld — erhält alle Sponsor-Anfragen.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="kvu_sponsoren_email2">Empfänger 2</label></th>
                    <td>
                        <input type="email" id="kvu_sponsoren_email2" name="kvu_sponsoren_email2"
                               value="<?php echo esc_attr( $email2 ); ?>" class="regular-text" />
                        <p class="description">Optional — leer lassen wenn nicht benötigt.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="kvu_sponsoren_email3">Empfänger 3</label></th>
                    <td>
                        <input type="email" id="kvu_sponsoren_email3" name="kvu_sponsoren_email3"
                               value="<?php echo esc_attr( $email3 ); ?>" class="regular-text" />
                        <p class="description">Optional — leer lassen wenn nicht benötigt.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'Speichern', 'primary', 'kvu_sponsoren_save' ); ?>
        </form>
    </div>
    <?php
}

// ── STYLES & SCRIPTS ─────────────────────────────────────────────────────────

function kvu_sponsoren_styles() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'kvu_sponsoren' ) ) return;
    echo '<style id="kvu-sponsoren-css">';
    include plugin_dir_path( __FILE__ ) . 'kvu-sponsoren-styles.css';
    echo '</style>';
}
add_action( 'wp_head', 'kvu_sponsoren_styles' );

function kvu_sponsoren_scripts() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'kvu_sponsoren' ) ) return;
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'kvu_sponsoren_scripts' );

// ── SHORTCODE ────────────────────────────────────────────────────────────────

add_shortcode( 'kvu_sponsoren', 'kvu_sponsoren_shortcode' );

function kvu_sponsoren_shortcode() {
    $nonce = wp_create_nonce( 'kvu_sponsoren_nonce' );
    $ajax  = admin_url( 'admin-ajax.php' );
    ob_start();
    ?>

    <!-- ── HERO ────────────────────────────────────────────────────────── -->
    <div class="sp-wrap">

    <section class="sp-hero">
        <div class="sp-hero-label">KV Untertürkheim 1906 e.V.</div>
        <h1>Partner &amp; <em>Sponsoren</em></h1>
        <p>Werden Sie Teil des ältesten Tennisclubs in Württemberg. Mit über 500 Mitgliedern, modernen Padel-Courts und einer lebendigen Gemeinschaft bieten wir Ihnen eine einzigartige Plattform.</p>
        <a href="#sp-pakete" class="sp-btn">Sponsorenpakete ansehen</a>
        <a href="#sp-kontakt" class="sp-btn-outline">Kontakt aufnehmen</a>
    </section>

    <!-- ── STATS ───────────────────────────────────────────────────────── -->
    <div class="sp-stats">
        <div class="sp-stats-inner">
            <div><span class="sp-stat-num">500+</span><div class="sp-stat-label">Mitglieder</div></div>
            <div><span class="sp-stat-num">120</span><div class="sp-stat-label">Jahre Vereinsgeschichte</div></div>
            <div><span class="sp-stat-num">12</span><div class="sp-stat-label">Tennisplätze &amp; Padel-Courts</div></div>
            <div><span class="sp-stat-num">50+</span><div class="sp-stat-label">Events pro Jahr</div></div>
        </div>
    </div>

    <!-- ── WARUM SPONSOR ────────────────────────────────────────────────── -->
    <section class="sp-section sp-section--cream">
        <div class="sp-inner">
            <div class="sp-section-label">Ihre Vorteile</div>
            <h2>Warum <em>Partner</em> des KVU?</h2>
            <p class="sp-intro">Als Sponsor des KV Untertürkheim verbinden Sie Ihr Unternehmen mit einem der traditionsreichsten Sportvereine der Region — und erreichen eine kaufkräftige, sportaffine Zielgruppe.</p>
            <div class="sp-why-grid">
                <div class="sp-why-card">
                    <div class="sp-why-icon">👁</div>
                    <h3>Sichtbarkeit</h3>
                    <p>Ihr Logo auf Website, Platzbannern, Spielkleidung und allen Vereinspublikationen. Tausende Sichtkontakte pro Saison.</p>
                </div>
                <div class="sp-why-card">
                    <div class="sp-why-icon">🤝</div>
                    <h3>Netzwerk</h3>
                    <p>Zugang zu einem exklusiven Netzwerk aus Unternehmern, Ärzten, Juristen und Entscheidungsträgern der Region Stuttgart.</p>
                </div>
                <div class="sp-why-card">
                    <div class="sp-why-icon">🏆</div>
                    <h3>Prestige</h3>
                    <p>Partner eines Bundesliga-Vereins mit Damen 1. und 2. Bundesliga. Positionieren Sie sich im Premium-Sport-Umfeld.</p>
                </div>
                <div class="sp-why-card">
                    <div class="sp-why-icon">📱</div>
                    <h3>Digital-Reichweite</h3>
                    <p>Präsenz auf Website und Social Media des KVU mit mehreren tausend Besuchern monatlich.</p>
                </div>
                <div class="sp-why-card">
                    <div class="sp-why-icon">🎾</div>
                    <h3>Hospitality</h3>
                    <p>Exklusive Einladungen zu Heimspielen der Bundesliga-Damen, Storchencup und weiteren Premium-Events.</p>
                </div>
                <div class="sp-why-card">
                    <div class="sp-why-icon">💚</div>
                    <h3>Gesellschaftliche Verantwortung</h3>
                    <p>Fördern Sie Jugendsport, Gesundheit und Gemeinschaft — und zeigen Sie, dass Ihr Unternehmen Verantwortung trägt.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ── PAKETE ───────────────────────────────────────────────────────── -->
    <section class="sp-section sp-section--white" id="sp-pakete">
        <div class="sp-inner">
            <div class="sp-section-label">Sponsoringpakete</div>
            <h2>Die richtigen <em>Pakete</em> für jeden Partner</h2>
            <p class="sp-intro">Von der gezielten Förderung bis zur umfassenden Hauptpartnerschaft — wählen Sie das Paket, das zu Ihren Zielen passt. Individuelle Arrangements sind jederzeit möglich.</p>
            <div class="sp-packages">

                <div class="sp-pkg">
                    <div class="sp-pkg-head">
                        <div class="sp-pkg-badge sp-badge--bronze">Förderer</div>
                        <div class="sp-pkg-name">Bronze</div>
                        <div class="sp-pkg-price">ab <strong>500 € / Jahr</strong></div>
                    </div>
                    <div class="sp-pkg-body">
                        <ul class="sp-pkg-features">
                            <li>Logo auf der Sponsorenseite (Website)</li>
                            <li>Namentliche Erwähnung im Vereinsnewsletter</li>
                            <li>2 Freikarten Bundesliga-Heimspiel</li>
                            <li class="sp-feat-no">Werbebanner auf Anlage</li>
                            <li class="sp-feat-no">Social-Media-Posts</li>
                            <li class="sp-feat-no">Hospitality-Paket</li>
                        </ul>
                    </div>
                    <a href="#sp-kontakt" class="sp-pkg-cta">Jetzt anfragen</a>
                </div>

                <div class="sp-pkg">
                    <div class="sp-pkg-head">
                        <div class="sp-pkg-badge sp-badge--silber">Partner</div>
                        <div class="sp-pkg-name">Silber</div>
                        <div class="sp-pkg-price">ab <strong>1.500 € / Jahr</strong></div>
                    </div>
                    <div class="sp-pkg-body">
                        <ul class="sp-pkg-features">
                            <li>Logo auf der Sponsorenseite (Website)</li>
                            <li>Namentliche Erwähnung im Vereinsnewsletter</li>
                            <li>4 Freikarten Bundesliga-Heimspiel</li>
                            <li>1 Werbebanner auf der Anlage</li>
                            <li>2 Social-Media-Posts pro Saison</li>
                            <li class="sp-feat-no">Hospitality-Paket</li>
                        </ul>
                    </div>
                    <a href="#sp-kontakt" class="sp-pkg-cta">Jetzt anfragen</a>
                </div>

                <div class="sp-pkg sp-pkg--featured">
                    <div class="sp-pkg-head">
                        <div class="sp-pkg-badge">⭐ Empfohlen</div>
                        <div class="sp-pkg-name">Gold</div>
                        <div class="sp-pkg-price">ab <strong>3.500 € / Jahr</strong></div>
                    </div>
                    <div class="sp-pkg-body">
                        <ul class="sp-pkg-features">
                            <li>Prominentes Logo auf Website</li>
                            <li>Präsenz in allen Vereinspublikationen</li>
                            <li>8 Freikarten Bundesliga-Heimspiel</li>
                            <li>2 Werbebanner auf der Anlage</li>
                            <li>Monatliche Social-Media-Erwähnung</li>
                            <li>Hospitality-Paket für 4 Personen</li>
                        </ul>
                    </div>
                    <a href="#sp-kontakt" class="sp-pkg-cta">Jetzt anfragen</a>
                </div>

                <div class="sp-pkg">
                    <div class="sp-pkg-head">
                        <div class="sp-pkg-badge sp-badge--platin">Hauptsponsor</div>
                        <div class="sp-pkg-name">Platin</div>
                        <div class="sp-pkg-price">ab <strong>7.500 € / Jahr</strong></div>
                    </div>
                    <div class="sp-pkg-body">
                        <ul class="sp-pkg-features">
                            <li>Exklusives Hauptsponsor-Logo überall</li>
                            <li>Naming-Right eines Events / Courts</li>
                            <li>Unbegrenzte Freikarten</li>
                            <li>3 Premium-Werbebanner inkl. Ballwand</li>
                            <li>Wöchentliche Social-Media-Präsenz</li>
                            <li>VIP-Hospitality für bis zu 10 Personen</li>
                        </ul>
                    </div>
                    <a href="#sp-kontakt" class="sp-pkg-cta">Jetzt anfragen</a>
                </div>

            </div>
            <p class="sp-packages-note">Alle Preise zzgl. MwSt. · Individuelle Pakete auf Anfrage möglich.</p>
        </div>
    </section>

    <!-- ── AKTUELLE SPONSOREN ────────────────────────────────────────────── -->
    <section class="sp-section sp-section--ivory">
        <div class="sp-inner">
            <div class="sp-section-label">Unsere Partner</div>
            <h2>Starke <em>Partnerunternehmen</em></h2>
            <p class="sp-intro">Wir danken unseren Sponsoren, die den KVU und den Sport in der Region Stuttgart aktiv unterstützen.</p>

            <div class="sp-logos-tier">
                <div class="sp-logos-tier-label">Platin-Partner</div>
                <div class="sp-logos-grid">
                    <div class="sp-logo-card sp-logo-card--platin">
                        <div class="sp-logo-placeholder"></div>
                        <div class="sp-logo-name">Ihr Unternehmen hier</div>
                    </div>
                </div>
            </div>

            <div class="sp-logos-tier">
                <div class="sp-logos-tier-label">Gold-Partner</div>
                <div class="sp-logos-grid">
                    <div class="sp-logo-card sp-logo-card--gold"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card sp-logo-card--gold"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card sp-logo-card--gold"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                </div>
            </div>

            <div class="sp-logos-tier">
                <div class="sp-logos-tier-label">Silber- &amp; Bronze-Partner</div>
                <div class="sp-logos-grid">
                    <div class="sp-logo-card"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                    <div class="sp-logo-card"><div class="sp-logo-placeholder"></div><div class="sp-logo-name">Ihr Unternehmen hier</div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── KONTAKT ───────────────────────────────────────────────────────── -->
    <section class="sp-cta-section" id="sp-kontakt">
        <h2>Werden Sie <em>Partner</em> des KVU</h2>
        <p>Wir freuen uns auf Ihre Anfrage. Gemeinsam finden wir das Sponsoring-Paket, das perfekt zu Ihnen passt.</p>

        <div class="sp-contact-box">
            <h3>Anfrage senden</h3>
            <div class="sp-contact-field">
                <label>Unternehmen *</label>
                <input type="text" id="sp-firma" placeholder="Mustermann GmbH" />
            </div>
            <div class="sp-contact-row">
                <div class="sp-contact-field">
                    <label>Vorname *</label>
                    <input type="text" id="sp-vorname" placeholder="Max" />
                </div>
                <div class="sp-contact-field">
                    <label>Nachname *</label>
                    <input type="text" id="sp-name" placeholder="Mustermann" />
                </div>
            </div>
            <div class="sp-contact-field">
                <label>E-Mail *</label>
                <input type="email" id="sp-email" placeholder="max@mustermann.de" />
            </div>
            <div class="sp-contact-field">
                <label>Paket-Interesse</label>
                <select id="sp-paket">
                    <option value="">Bitte auswählen …</option>
                    <option>Bronze – Förderer (ab 500 €)</option>
                    <option>Silber – Partner (ab 1.500 €)</option>
                    <option>Gold – Partner (ab 3.500 €)</option>
                    <option>Platin – Hauptsponsor (ab 7.500 €)</option>
                    <option>Individuelles Arrangement</option>
                </select>
            </div>
            <div class="sp-contact-field">
                <label>Nachricht</label>
                <textarea id="sp-nachricht" placeholder="Ihre Fragen oder Wünsche …"></textarea>
            </div>
            <div id="sp-form-msg" class="sp-form-msg"></div>
            <button id="sp-submit" class="sp-contact-submit">Anfrage senden →</button>
        </div>

        <p class="sp-cta-footer">KV Untertürkheim 1906 e.V. · Im Dietbach 3, 70734 Fellbach · info@kv-untertuerkheim.de</p>
    </section>

    </div><!-- .sp-wrap -->

    <script>
    (function($){
        $('#sp-submit').on('click', function(){
            var btn = $(this);
            var msg = $('#sp-form-msg');
            msg.removeClass('sp-msg-ok sp-msg-err').text('');

            var data = {
                action:    'kvu_sponsoren_anfrage',
                nonce:     '<?php echo esc_js( $nonce ); ?>',
                firma:     $('#sp-firma').val(),
                vorname:   $('#sp-vorname').val(),
                name:      $('#sp-name').val(),
                email:     $('#sp-email').val(),
                paket:     $('#sp-paket').val(),
                nachricht: $('#sp-nachricht').val()
            };

            btn.prop('disabled', true).text('Wird gesendet …');
            $.post('<?php echo esc_js( $ajax ); ?>', data, function(res){
                if (res.success) {
                    msg.addClass('sp-msg-ok').text(res.data.msg);
                    $('#sp-firma, #sp-vorname, #sp-name, #sp-email, #sp-nachricht').val('');
                    $('#sp-paket').val('');
                } else {
                    msg.addClass('sp-msg-err').text(res.data.msg);
                }
            }).fail(function(){
                msg.addClass('sp-msg-err').text('Verbindungsfehler. Bitte versuchen Sie es erneut.');
            }).always(function(){
                btn.prop('disabled', false).text('Anfrage senden →');
            });
        });
    })(jQuery);
    </script>

    <?php
    return ob_get_clean();
}
