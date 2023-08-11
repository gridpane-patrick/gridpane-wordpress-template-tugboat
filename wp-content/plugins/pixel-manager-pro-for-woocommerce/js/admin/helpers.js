jQuery(function () {

	// copy debug info textarea
	jQuery("#debug-info-button").on("click", function () {
		jQuery("#debug-info-textarea").select()
		document.execCommand("copy")
	})

	jQuery("#wpm_pro_version_demo").on("click", function () {
		jQuery("#submit").trigger("click")
	})

	document.getElementById("json-settings-file-input")
		.addEventListener("change", wpm.readSingleFile, false)
});

(function (wpm, $, undefined) {

	wpm.saveSettingsToDisk = () => {
		let text             = document.getElementById("export-settings-json").value
		text                 = text.replace(/\n/g, "\r\n") // To retain the Line breaks.
		let blob             = new Blob([text], {type: "text/plain"})
		let anchor           = document.createElement("a")
		anchor.download      = "pixel-manager-settings-" + wpm.getCurrentDateForFileName() + ".json"
		anchor.href          = window.URL.createObjectURL(blob)
		anchor.target        = "_blank"
		anchor.style.display = "none" // just to be safe!
		document.body.appendChild(anchor)
		anchor.click()
		document.body.removeChild(anchor)
	}

	// Get date in year month day divided by dots. Month and day have to be zero padded.
	wpm.getCurrentDateForFileName = () => {
		let date = new Date()
		let year  = date.getFullYear()
		let month = ("0" + (date.getMonth() + 1)).slice(-2)
		let day   = ("0" + date.getDate()).slice(-2)
		return year + "." + month + "." + day

		// return date.toLocaleDateString(
		// 	"en-US", {
		// 		year : "numeric",
		// 		month: "2-digit",
		// 		day  : "2-digit",
		// 	},
		// )
	}

	wpm.readSingleFile = (e) => {

		let file = e.target.files[0]
		if (!file) return
		let reader    = new FileReader()
		reader.onload = function (e) {
			let contents = JSON.parse(e.target.result)

			// document.getElementById("import-settings-json").textContent = JSON.stringify(contents)

			wpm.saveImportedSettingsToDb(contents)
		}
		reader.readAsText(file)
	}

	wpm.saveImportedSettingsToDb = (settings) => {

		let data = {
			action  : "wpm_save_imported_settings",
			settings: settings,
		}

		jQuery.ajax(
			{
				type    : "post",
				dataType: "json",
				url     : ajaxurl,
				data    : data,
				success : async (msg) => {
					if (msg.success) {
						console.log(msg)
						// reload window
						document.getElementById("upload-status-success").style.display = "block"
						// wait 5 seconds
						await new Promise(res => setTimeout(res, 5000))
						window.location.reload()
					} else {
						console.log(msg)

						document.getElementById("upload-status-error").style.display = "block"
					}

				},
				error   : function (msg) {
					console.log("Somethings went wrong: " + msg)

					document.getElementById("upload-status-error").style.display = "block"

					// console.log(msg);
				},
			})

	}


}(window.wpm = window.wpm || {}, jQuery))


