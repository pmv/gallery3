<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2013 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Gallery_View_Admin extends View_Gallery {
  /**
   * Attempts to load a view and pre-load view data.
   *
   * @throws  Kohana_Exception  if the requested view cannot be found
   * @param   string  $name view name
   * @param   string  $theme_name view name
   * @return  void
   */
  public function __construct($name) {
    parent::__construct($name);

    $this->theme_name = Module::get_var("gallery", "active_admin_theme");
    if (Identity::active_user()->admin) {
      $theme_name = Request::current()->query("theme");
      if ($theme_name &&
          file_exists(THEMEPATH . $theme_name) &&
          strpos(realpath(THEMEPATH . $theme_name), THEMEPATH) == 0) {
        $this->theme_name = $theme_name;
      }
    }
    $this->sidebar = "";
    $this->set_global(array("theme" => $this,
                            "user" => Identity::active_user(),
                            "page_type" => "admin",
                            "page_subtype" => $name,
                            "page_title" => null));
  }

  public function admin_menu() {
    $menu = Menu::factory("root");
    Module::event("admin_menu", $menu, $this);

    $settings_menu = $menu->get("settings_menu");
    uasort($settings_menu->elements, array("Menu", "title_comparator"));

    return $menu->render();
  }

  public function user_menu() {
    $menu = Menu::factory("root")
      ->css_id("g-login-menu")
      ->css_class("g-inline ui-helper-clear-fix");
    Module::event("user_menu", $menu, $this);
    return $menu->render();
  }

  /**
   * Print out any site wide status information.
   */
  public function site_status() {
    return SiteStatus::get();
  }

  /**
   * Print out any messages waiting for this user.
   */
  public function messages() {
    return Message::get();
  }

 /**
   * Handle all theme functions that insert module content.
   */
  public function __call($function, $args) {
    switch ($function) {
    case "admin_credits";
    case "admin_footer":
    case "admin_header_top":
    case "admin_header_bottom":
    case "admin_page_bottom":
    case "admin_page_top":
    case "admin_head":
    case "body_attributes":
    case "html_attributes":
      array_unshift($args, $this);  // put $this as the first argument
      $blocks = Gallery::hook("Theme", $function, $args);

      if (Session::instance()->get("debug")) {
        if ($function != "admin_head" && $function != "body_attributes") {
          array_unshift(
            $blocks,
            "<div class=\"g-annotated-theme-block g-annotated-theme-block_$function g-clear-fix\">" .
            "<div class=\"title\">$function</div>");
          $blocks[] = "</div>";
        }
      }
      return implode("\n", $blocks);

    default:
      throw new Gallery_Exception("Unknown theme function: $function");
    }
  }
}