import { A11yUtils } from "../utils/a11y";
import "wicg-inert";

declare global {
  interface Window {
    dataLayer: any;
    _mtm: any;
  }
}

export class CookieComponent {
  private consentCookie = "__cookie_consent";
  private cookieBlocked = "__cookie_blocked";
  private mainContentBlock: HTMLElement;
  private cookieModal: HTMLElement;

  constructor() {
    let shouldRun = false;

    if (
      /bot|google|baidu|bing|msn|duckduckbot|teoma|slurp|yandex/i.test(
        navigator.userAgent
      )
    ) {
      shouldRun = false;
    } else {
      shouldRun = this.getCookie(this.consentCookie) ? false : true;
    }

    this.mainContentBlock = document.getElementById("mainContentBlock");

    const cookieBanner = document.getElementById("cookiebanner");

    if (shouldRun && cookieBanner) {
      cookieBanner.classList.remove("hidden");
      const overlay = document.getElementById("cookiebanner-overlay");
      overlay.classList.remove("hidden");

      A11yUtils.keepFocus(cookieBanner);
      cookieBanner.focus();
      const closeBtn = document.querySelector(
        ".js-modal-close-btn"
      ) as HTMLElement;
      if (closeBtn) {
        closeBtn.setAttribute("disabled", "true");
        closeBtn.classList.add("hidden");
      }
      this.setMainContentInert();
      this.triggerEvent("cookie-banner-opened");

      setTimeout(() => {
        if (
          window.getComputedStyle(overlay).display === "none" ||
          window.getComputedStyle(cookieBanner).display === "none"
        ) {
          console.info("A browser plugin blocked the cookie banner");
          this.setMainContentInert(false);
          this.setCookie(this.consentCookie, "365", false);
          this.setCookie(this.cookieBlocked, "365", true);
          this.triggerEvent("cookie-banner-blocked");
          this.triggerEvent("cookie-closed");
        }
      }, 500);
    }

    document.body.addEventListener("click", this.trackingListener.bind(this));
    document.body.addEventListener("click", this.clickListener.bind(this));
  }

    private trackingListener(event: Event) {
      var element = event.target as HTMLElement;
      if (!element) {
          return;
      }

      if (element.classList.contains("js-cookie-settings")) {
          event.preventDefault();
          this.cookieRatio('settings');
      } else if (element.classList.contains("js-cookie-accept")) {
          event.preventDefault();
          this.cookieRatio('accept');
      } else if(element.classList.contains('js-cookie-essentials')){
          this.cookieRatio('deny');
      }
    }

    private cookieRatio(choice: string) {

       let promise = new Promise((resolve, reject) => {
          const xhr = new XMLHttpRequest();
          const url = "cookie-tracking/add-choice-to-database";
          var params = "response=" + choice;

          xhr.open("POST", url, true);
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

          xhr.onreadystatechange = function () {
              if (xhr.readyState === 4 && xhr.status === 200) {
                  // console.log("Click count updated successfully");
              }
          };

          xhr.onerror = function () {
              // console.error("Network error");
              reject("Network error");
          };

          xhr.send(params);
      });
  }

  private clickListener(event) {
    const element = event.target;
    if (!element) {
      return;
    }

    if (element.classList) {
      if (element.classList.contains("js-cookie-settings")) {
        event.preventDefault();
        const cookieBanner = document.getElementById("cookiebanner");
        if (cookieBanner) {
          cookieBanner.classList.add("hidden");
        }
        this.renderCookieModal();
        setTimeout(() => {
          if (window.getComputedStyle(this.cookieModal).display == "none") {
            alert("Cookie settings modal is blocked by a browser plugin");
          } else {
            this.setMainContentInert();
          }
        }, 500);
      } else if (element.classList.contains("js-cookie-essentials")) {
        event.preventDefault();
        this.setCookie(this.consentCookie, "365", false);
        document.getElementById("cookiebanner").classList.add("hidden");
        document.getElementById("cookiebanner-overlay").classList.add("hidden");
        document.getElementById("cookieModal").classList.add("hidden");
        this.setMainContentInert(false);
        this.triggerEvent("cookie-closed");
      } else if (element.classList.contains("js-cookie-accept")) {
        event.preventDefault();
        this.setCookie(this.consentCookie, "365", true);
        document.getElementById("cookiebanner").classList.add("hidden");
        document.getElementById("cookiebanner-overlay").classList.add("hidden");
        document.getElementById("cookieModal").classList.add("hidden");
        this.setMainContentInert(false);
        this.triggerEvent("cookie-closed");
      } else if (element.classList.contains("js-modal-close")) {
        event.preventDefault();
        this.closeCookieModal();
        document.getElementById("cookiebanner-overlay").classList.add("hidden");
      } else if (element.classList.contains("js-modal-close-btn")) {
        event.preventDefault();
        this.closeCookieModalWithoutSave();
        document.getElementById("cookiebanner-overlay").classList.add("hidden");
      } else if (element.classList.contains("js-cookie-performance")) {
        this.updateCheckbox("performance");
      } else if (element.classList.contains("js-cookie-marketing")) {
        this.updateCheckbox("marketing");
      }
    }
  }

  private closeCookieModal() {
    if (
      this.isCookieChecked("performance") == true &&
      this.isCookieChecked("marketing") == true
    ) {
      this.setCookie(this.consentCookie, "365", true);
    }
    if (
      this.isCookieChecked("performance") == true &&
      this.isCookieChecked("marketing") == false
    ) {
      this.setCookie(this.consentCookie, "365", 2);
    }

    if (
      this.isCookieChecked("marketing") == true &&
      this.isCookieChecked("performance") == false
    ) {
      this.setCookie(this.consentCookie, "365", 3);
    }

    if (
      this.isCookieChecked("marketing") == false &&
      this.isCookieChecked("performance") == false
    ) {
      this.setCookie(this.consentCookie, "365", false);
    }

    const cookieModal = document.getElementById("cookieModal");
    cookieModal.classList.toggle("hidden");
    this.setMainContentInert(false);

    this.triggerEvent("cookie-closed");
  }

  private closeCookieModalWithoutSave() {
    const cookieModal = document.getElementById("cookieModal");
    cookieModal.classList.toggle("hidden");
    this.setMainContentInert(false);

    this.triggerEvent("cookie-closed");
  }

  private updateCheckbox(label, init = false) {
    const checkboxvar = document.getElementById(label) as HTMLInputElement;

    if (
      (checkboxvar.defaultChecked && !checkboxvar.checked) ||
      !checkboxvar.checked
    ) {
      checkboxvar.checked = false;
      checkboxvar.defaultChecked = false;
      if (!init) {
        this.triggerEvent(`cookie-prop-${label}-disabled`);
      }
    } else {
      checkboxvar.checked = true;
      if (!init) {
        this.triggerEvent(`cookie-prop-${label}-enabled`);
      }
    }
  }

  private isCookieChecked(cookie) {
    const cookieId = document.getElementById(cookie) as HTMLInputElement;
    if (cookieId.checked == true || cookieId.defaultChecked) {
      return true;
    } else {
      return false;
    }
  }

  private getCookie(key) {
    if (!key) {
      return null;
    }
    return (
      decodeURIComponent(
        document.cookie.replace(
          new RegExp(
            "(?:(?:^|.*;)\\s*" +
              encodeURIComponent(key).replace(/[\-\.\+\*]/g, "\\$&") +
              "\\s*\\=\\s*([^;]*).*$)|^.*$"
          ),
          "$1"
        )
      ) || null
    );
  }

  private setCookie(key, expireDays, value) {
    const date = new Date();
    if (expireDays) {
      date.setTime(date.getTime() + expireDays * 24 * 60 * 60 * 1000);
    }
    let expires = date.toUTCString();
    document.cookie =
      encodeURIComponent(key) +
      "=" +
      encodeURIComponent(value) +
      (expires ? "; expires=" + expires : "") +
      "; path=/";

    if (window.dataLayer) {
      window.dataLayer.push({ event: "cookie_refresh" });
    }

    if (window._mtm) {
      window._mtm.push({ event: 'cookie_refresh' });
    }
  }

  private renderCookieModal() {
    //check if the modal was already opened before
    const cookieBanner = document.getElementById("cookiebanner");
    if (cookieBanner) {
      cookieBanner.classList.add("hidden");
    }
    this.cookieModal = document.getElementById("cookieModal");
    if (this.cookieModal) {
      this.cookieModal.classList.remove("hidden");
      this.triggerEvent("cookie-modal-opened");
    }
    var cookieOverlay = document.getElementById("cookiebanner-overlay");
    cookieOverlay.classList.remove("hidden");

    A11yUtils.keepFocus(this.cookieModal);
    this.cookieModal.focus();

    const cookieGdpr = this.getCookie(this.consentCookie);

    if (cookieGdpr == "true") {
      (document.getElementById("performance") as HTMLInputElement).checked =
        true;
      this.updateCheckbox("performance", true);
      (document.getElementById("marketing") as HTMLInputElement).checked = true;
      this.updateCheckbox("marketing", true);
    }
    if (cookieGdpr == "2") {
      (document.getElementById("performance") as HTMLInputElement).checked =
        true;
      this.updateCheckbox("performance", true);
    }
    if (cookieGdpr == "3") {
      (document.getElementById("marketing") as HTMLInputElement).checked = true;
      this.updateCheckbox("marketing", true);
    }
  }

  private setMainContentInert(set = true) {
    if (this.mainContentBlock && set) {
      this.mainContentBlock.setAttribute("inert", "");
      document.documentElement.classList.add("overflow-hidden");
    }
    if (this.mainContentBlock && !set) {
      this.mainContentBlock.removeAttribute("inert");
      document.documentElement.classList.remove("overflow-hidden");
    }
  }

  private triggerEvent(eventName: string) {
    const event = new Event(eventName);
    window.dispatchEvent(event);
  }
}
