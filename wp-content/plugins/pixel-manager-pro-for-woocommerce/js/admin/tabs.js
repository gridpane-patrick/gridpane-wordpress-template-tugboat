jQuery(function () {

	 // Don't run if we are not one of the
	 // WPM main tabs
	if("wpm" !== wpmGetPageId()) return

	let sections    = []
	let subsections = {}

	// Hide unnecessary elements
	jQuery(".section").closest("tr").hide()

	// Collect information on sections
	jQuery(".section").each(function () {
		sections.push({
			"slug" : jQuery(this).data("sectionSlug"),
			"title": jQuery(this).data("sectionTitle"),
		})
	})

	// Collect information on subsections
	jQuery(".subsection").each(function () {

		subsections[jQuery(this).data("sectionSlug")] = subsections[jQuery(this).data("sectionSlug")] || []

		subsections[jQuery(this).data("sectionSlug")].push({
			"title": jQuery(this).data("subsectionTitle"),
			"slug" : jQuery(this).data("subsectionSlug"),
		})
	})

	// Create tabs for sections
	sections.forEach(
		function (section) {
			jQuery(".nav-tab-wrapper").append("<a href=\"#\" class=\"nav-tab\" data-section-slug=\"" + section["slug"] + "\">" + section["title"] + "</a>")
		})

	// Create tabs for each subsections
	jQuery(".nav-tab-wrapper").after(wpmCreateSubtabUlHtml(subsections))

	// Create on-click events on section tabs that toggle the views
	jQuery(".nav-tab-wrapper a").on("click", function (e) {

		e.preventDefault()

		// show clicked tab as active
		jQuery(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active")

		// toggle the sections visible / invisible based on clicked tab

		let sectionSlug = jQuery(this).data("section-slug")
		wpmToggleSections(sectionSlug, sections)

		// if subsection exists, click on first subsection
		if (sectionSlug in subsections) {
			jQuery("ul[data-section-slug=" + sectionSlug + "]").children(":first").trigger("click")
		}
	})

	// Create on-click events on subsection tabs that toggle the views
	jQuery(".subnav-li").on("click", function (e) {

		e.preventDefault()

		// jQuery(this).hide();
		jQuery(this)
			.addClass("subnav-li-active").removeClass("subnav-li-inactive")
			.siblings()
			.addClass("subnav-li-inactive").removeClass("subnav-li-active")

		wpmToggleSubsection(jQuery(this).parent().data("section-slug"), jQuery(this).data("subsection-slug"))
	})

	/**
	 * If someone accesses a plugin tab by deep link, open the right tab
	 * or fallback to default (first tab)
	 *
	 * If deeplink is being opened,
	 * open the according section and subsection
	 */
	if (wpmGetSectionParams()) {

		let sectionParams = wpmGetSectionParams()

		jQuery("a[data-section-slug=" + sectionParams["section"] + "]").trigger("click")

		if (sectionParams["subsection"] !== false) {
			jQuery("ul[data-section-slug=" + sectionParams["section"] + "]").children("[data-subsection-slug=" + sectionParams["subsection"] + "]").trigger("click")
		}
	} else {
		jQuery("a[data-section-slug=" + sections[0]["slug"] + "]").trigger("click")
	}
})

// Creates the html with all subsection elements
function wpmCreateSubtabUlHtml(subsections) {

	let subsectionsKeys = Object.keys(subsections)

	let html = ""

	subsectionsKeys.forEach(function (subsectionKey) {
		html += "<ul class=\"subnav-tabs\" data-section-slug=\"" + subsectionKey + "\">"

		let subtabs = subsections[subsectionKey]

		subtabs.forEach(function (subtab) {
			html += "<li class=\"subnav-li subnav-li-inactive\" style=\"cursor: pointer;\" data-subsection-slug=\"" + subtab["slug"] + "\">" + subtab["title"] + "</li>"
		})

		html += "</ul>"
	})

	return html
}

/**
 * If section (and subsection) URL parameters are set,
 * return them, otherwise return false
 */
function wpmGetSectionParams() {

	const queryString = window.location.search
	const urlParams   = new URLSearchParams(queryString)

	if (urlParams.get("section")) {
		return {
			"section"   : urlParams.get("section"),
			"subsection": urlParams.get("subsection"),
		}
	} else {
		return false
	}
}

// Toggles the sections
function wpmToggleSections(sectionSlug, sections) {

	jQuery("#wpm_settings_form > h2").nextUntil(".submit").andSelf().hide()
	jQuery(".subnav-tabs").hide()
	jQuery(".subnav-tabs[data-section-slug=" + sectionSlug + "]").show()

	let sectionPos = sections.findIndex((arrayElement) => arrayElement["slug"] === sectionSlug)

	jQuery("div[data-section-slug=" + sectionSlug + "]").closest("table").prevAll("h2:first").next().nextUntil("h2, .submit").andSelf().show()

	// set the URL with the active tab parameter
	wpmSetUrl(sections[sectionPos]["slug"])
}

function wpmToggleSubsection(sectionSlug, subsectionSlug) {

	jQuery("#wpm_settings_form > h2").nextUntil(".submit").andSelf().hide()
	jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("tr").siblings().andSelf().hide()

	jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("table").show()
	jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug=" + subsectionSlug + "]").closest("tr").nextUntil(jQuery("[data-section-slug=" + sectionSlug + "][data-subsection-slug]").closest("tr")).show()

	// Set the URL with the active tab parameter
	wpmSetUrl(sectionSlug, subsectionSlug)
}

// Sets the new URL parameters
function wpmSetUrl(sectionSlug, subsectionSlug = "") {

	const queryString = window.location.search
	const urlParams   = new URLSearchParams(queryString)

	urlParams.delete("section")
	urlParams.delete("subsection")

	let newParams = "section=" + sectionSlug
	newParams += subsectionSlug ? "&subsection=" + subsectionSlug : ""

	history.pushState("", "wpm" + sectionSlug, document.location.pathname + "?page=wpm&" + newParams)

	// Make WP remember which was the selected tab on a save and return to the same tab after saving
	jQuery("input[name =\"_wp_http_referer\"]").val(wpmGetAdminPath() + "?page=wpm&" + newParams + "&settings-updated=true")
}

function wpmGetAdminPath() {
	let url = new URL(jQuery("#wp-admin-canonical").attr("href"))
	return url.pathname
}

wpmGetPageId = () => {

	const queryString = window.location.search
	const urlParams   = new URLSearchParams(queryString)

	return urlParams.get("page")
}

