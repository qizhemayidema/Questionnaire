$.fn.extend({
	"initUpload": function(opt) {
		if (typeof opt != "object") {
			return;
		}
		var uploadId = $(this).attr("class");
		if (uploadId == null || uploadId == "") {}
		$.each(uploadTools.getInitOption(uploadId), function(key, value) {
			if (opt[key] == null) {
				opt[key] = value;
			}
		});
		if (opt.autoCommit) {
			opt.isHiddenUploadBt = true;
		}
		uploadTools.flushOpt(opt);
		uploadTools.initWithLayout(opt);
		uploadTools.initWithDrag(opt);
		uploadTools.initWithSelectFile(opt);
		uploadTools.initWithUpload(opt);
		uploadTools.initWithCleanFile(opt);
		uploadFileList.initFileList(opt);
	}
});
var uploadTools = {
	"imgArray": ['jpg', 'png', 'jpeg', 'bmp', 'gif', 'webp'],
	"getInitOption": function(uploadId) {
		var initOption = {
			"uploadId": uploadId,
			"uploadUrl": "#",
			"progressUrl": "#",
			"scheduleStandard": false,
			"selfUploadBtId": "",
			"rememberUpload": false,
			"velocity": 10,
			"autoCommit": false,
			"isHiddenUploadBt": false,
			"isHiddenCleanBt": false,
			"isAutoClean": false,
			"canDrag": true,
			"fileType": "*",
			"size": "-1",
			"ismultiple": true,
			"showSummerProgress": true,
			"showFileItemProgress": true,
			"filelSavePath": "",
			"beforeUpload": function() {},
			"onUpload": function() {}
		};
		return initOption;
	},
	"initWithUpload": function(opt) {
		var uploadId = opt.uploadId;
		if (!opt.isHiddenUploadBt) {
			$("#" + uploadId + " .uploadBts .uploadFileBt").off();
			$("#" + uploadId + " .uploadBts .uploadFileBt").on("click", function() {
				uploadEvent.uploadFileEvent(opt);
			});
			$("#" + uploadId + " .uploadBts .uploadFileBt i").css("color", "#0099FF");
		}
		if (opt.selfUploadBtId != null && opt.selfUploadBtId != "") {
			if (uploadTools.foundExitById(opt.selfUploadBtId)) {
				$("#" + opt.selfUploadBtId).off();
				$("#" + opt.selfUploadBtId).on("click", function() {
					uploadEvent.uploadFileEvent(opt);
				});
			}
		}
	},
	"foundExitById": function(id) {
		return $("#" + id).size() > 0;
	},
	"initWithCleanFile": function(opt) {
		var uploadId = opt.uploadId;
		if (!opt.isHiddenCleanBt) {
			$("#" + uploadId + " .uploadBts .cleanFileBt").off();
			$("#" + uploadId + " .uploadBts .cleanFileBt").on("click", function() {
				uploadEvent.cleanFileEvent(opt);
			});
			$("#" + uploadId + " .uploadBts .cleanFileBt i").css("color", "#0099FF");
		}
	},
	"initWithSelectFile": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId + " .uploadBts .selectFileBt").css("background-color", "#0099FF");
		$("#" + uploadId + " .uploadBts .selectFileBt").off();
		$("#" + uploadId + " .uploadBts .selectFileBt").on("click", function() {
			if (opt.autoCommit) {
				uploadEvent.cleanFileEvent(opt);
			}
			uploadEvent.selectFileEvent(opt);
		});
	},
	"getShowFileType": function(isImg, fileType, fileName, isImgUrl, fileCodeId) {
		var showTypeStr = "<div class='fileType'>" + fileType + "</div> <i class='iconfont icon-wenjian'></i>";
		var modelStr = "";
		if (isImg) {
			if (isImgUrl != null && isImgUrl != "null" && isImgUrl != "") {
				showTypeStr = "<img src='" + isImgUrl + "'/>";
				modelStr += "<div class='fileItem'  fileCodeId='" + fileCodeId + "'>";
				modelStr += "<div class='imgShow imgShow1'>";
				modelStr += showTypeStr;
				modelStr += " </div>";
				modelStr += " <div class='progress'>";
				modelStr += "<div class='progress_inner'></div>";
				modelStr += "</div>";
				modelStr += "<div class='status'>";
				modelStr += "<i class='iconfont icon-shanchu'></i>";
				modelStr += "</div>";
				modelStr += " <div class='fileName' >";
				modelStr += fileName;
				modelStr += "</div>";
				modelStr += " </div>";
			}
		} else {
			modelStr += "<div class='fileItem'  fileCodeId='" + fileCodeId + "'>";
			modelStr += "<div class='imgShow'>";
			modelStr += showTypeStr;
			modelStr += " </div>";
			modelStr += " <div class='progress'>";
			modelStr += "<div class='progress_inner'></div>";
			modelStr += "</div>";
			modelStr += "<div class='status'>";
			modelStr += "<i class='iconfont icon-shanchu'></i>";
			modelStr += "</div>";
			modelStr += " <div class='fileName'>";
			modelStr += fileName;
			modelStr += "</div>";
			modelStr += " </div>";
		}

		return modelStr;
	},
	"initWithLayout": function(opt) {
		var uploadId = opt.uploadId;
		var btsStr = "";
		btsStr += "<div class='uploadBts'>";
		btsStr += "<div>";
		btsStr += "<div class='selectFileBt'>上传文件</div>";
		btsStr += "</div>";
		if (!opt.isHiddenUploadBt) {
			btsStr += "<div class='uploadFileBt'>";
			btsStr += "<i class='iconfont icon-shangchuan'></i>";
			btsStr += " </div>";
		}
		if (!opt.isHiddenCleanBt) {
			btsStr += "<div class='cleanFileBt'>";
			btsStr += "<i class='iconfont icon-qingchu'></i>";
			btsStr += " </div>";
		}
		btsStr += "</div>";
		$("#" + uploadId).append(btsStr);
		if (opt.showSummerProgress) {
			var summerProgressStr = "<div class='subberProgress'>";
			summerProgressStr += "<div class='progress'>";
			summerProgressStr += "<div>0%</div>";
			summerProgressStr += "</div>";
			summerProgressStr += " </div>";
			$("#" + uploadId).append(summerProgressStr);
		}
		var boxStr = "<div class='box'> <ul class='BoxLeft'></ul> <ul class='BoxRight'></ul></div>";
		$("#" + uploadId).append(boxStr);
	},
	"initWithDrag": function(opt) {
		var canDrag = opt.canDrag;
		var uploadId = opt.uploadId;
		if (canDrag) {
			$(document).on({
				dragleave: function(e) {
					e.preventDefault();
				},
				drop: function(e) {
					e.preventDefault();
				},
				dragenter: function(e) {
					e.preventDefault();
				},
				dragover: function(e) {
					e.preventDefault();
				}
			});
			var box = $("#" + uploadId + " .box").get(0);
			if (box != null) {
				box.addEventListener("drop", function(e) {
					if ($("#" + uploadId).attr("isUpload") == "true") {
						e.preventDefault();
					} else {
						uploadEvent.dragListingEvent(e, opt);
					}
				});
			}
		}
	},
	"initWithDeleteFile": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId + " .fileItem .status i").off();
		$("#" + uploadId + " .fileItem .status i").on("click", function() {
			uploadEvent.deleteFileEvent(opt, this);
		})
	},
	"getSuffixNameByFileName": function(fileName) {
		var str = fileName;
		var pos = str.lastIndexOf(".") + 1;
		var lastname = str.substring(pos, str.length);
		return lastname;
	},
	"isInArray": function(strFound, arrays) {
		var ishave = false;
		for (var i = 0; i < arrays.length; i++) {
			if (strFound == arrays[i] || strFound.toLowerCase() == arrays[i]) {
				ishave = true;
				break;
			}
		}
		return ishave;
	},
	"fileIsExit": function(file, opt) {
		var fileList = uploadFileList.getFileList(opt);
		var ishave = false;
		for (var i = 0; i < fileList.length; i++) {
			if (fileList[i] != null && fileList[i].name == file.name && fileList[i].size == file.size) {
				ishave = true;
			}
		}
		return ishave;
	},
	"fileIsHaveUpload": function(file, opt) {
		var fileList = opt.rememberFile;
		var ishave = false;
		if (fileList != null) {
			for (var i = 0; i < fileList.length; i++) {
				if (fileList[i] != null && fileList[i].name == file.name && fileList[i].size == file.size) {
					ishave = true;
				}
			}
		}
		return ishave;
	},
	"addFileList": function(fileList, opt) {
		var uploadId = opt.uploadId;
		var boxJsObj = $("#" + uploadId + " .box").get(0);
		var fileListArray = uploadFileList.getFileList(opt);
		var fileNumber = uploadTools.getFileNumber(opt);
		if (fileNumber + fileList.length > opt.maxFileNumber) {
			return;
		}
		var imgtest = /image\/(\w)*/;
		var fileTypeArray = opt.fileType;
		var fileSizeLimit = opt.size;
		for (var i = 0; i < fileList.length; i++) {
			if (uploadTools.fileIsExit(fileList[i], opt)) {
				continue;
			}
			if (opt.rememberUpload) {
				if (uploadTools.fileIsHaveUpload(fileList[i], opt)) {
					continue;
				}
			}
			var fileTypeStr = uploadTools.getSuffixNameByFileName(fileList[i].name);
			if (fileSizeLimit != -1 && fileList[i].size > (fileSizeLimit * 1000)) {
				continue;
			}
			if (fileTypeArray == "*" || uploadTools.isInArray(fileTypeStr, fileTypeArray)) {
				var fileTypeUpcaseStr = fileTypeStr.toUpperCase();
				if (imgtest.test(fileList[i].type)) {
					var imgUrlStr = "";
					if (window.createObjectURL != undefined) {
						imgUrlStr = window.createObjectURL(fileList[i]);
					} else if (window.URL != undefined) {
						imgUrlStr = window.URL.createObjectURL(fileList[i]);
					} else if (window.webkitURL != undefined) {
						imgUrlStr = window.webkitURL.createObjectURL(fileList[i]);
					}
					var fileModel = uploadTools.getShowFileType(true, fileTypeUpcaseStr, fileList[i].name, imgUrlStr, fileListArray.length);

					if ($('.BoxLeft').height() < $('.BoxRight').height()) {
						$('.BoxLeft').append(fileModel);
					} else {
						$('.BoxRight').append(fileModel);
					}
					//$(boxJsObj).append(fileModel);
				} else {
					var fileModel = uploadTools.getShowFileType(false, fileTypeUpcaseStr, fileList[i].name, null, fileListArray.length);
					//$(boxJsObj).append(fileModel);
					if ($('.BoxLeft').height() < $('.BoxRight').height()) {
						$('.BoxLeft').append(fileModel);
					} else {
						$('.BoxRight').append(fileModel);
					}
				}
				uploadTools.initWithDeleteFile(opt);
				fileListArray[fileListArray.length] = fileList[i];
			} else {
				mui.alert("不允许上传此格式:" + fileList[i].name);
			}
		}
		uploadFileList.setFileList(fileListArray, opt);
	},
	"cleanFilInputWithSelectFile": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId + "_file").remove();
	},
	"showUploadProgress": function(opt, bytesRead, percent) {
		var uploadId = opt.uploadId;
		var fileListArray = uploadFileList.getFileList(opt);
		if (opt.showSummerProgress) {
			var progressBar = $("#" + uploadId + " .subberProgress .progress>div");
			progressBar.css("width", percent + "%");
			progressBar.html(percent + "%");
			if (percent == 100) {
				if (opt.isAutoClean) {
					setTimeout(function() {
						uploadEvent.cleanFileEvent(opt);
					}, 2000);
				}
			}
		}
		for (var i = 0; i < fileListArray.length; i++) {
			if (fileListArray[i] == null) {
				continue;
			}
			var testbytesRead = bytesRead - fileListArray[i].size;
			if (testbytesRead < 0) {
				if (percent == 100) {
					if (opt.showFileItemProgress) {
						$("#" + uploadId + " .box .fileItem[fileCodeId='" + i + "'] .progress>div").addClass("error");
						$("#" + uploadId + " .box .fileItem[fileCodeId='" + i + "'] .progress>div").css("width", "100%");
					}
					$("#" + uploadId + " .box .fileItem[fileCodeId='" + i + "'] .status>i").addClass("iconfont icon-cha");
					bytesRead = bytesRead - fileListArray[i].size;
				} else {

					if (opt.showFileItemProgress) {
						$("#" + uploadId + " .box .fileItem[fileCodeId='" + i + "'] .progress>div").css("width", (bytesRead /
							fileListArray[i].size * 100) + "%");
					}
					break;
				}
			} else if (testbytesRead >= 0) {		
				if (opt.showFileItemProgress) {
					$("#" + uploadId + " .box .fileItem[fileCodeId='" + i + "'] .progress>div").css("width", "100%");
				}
				bytesRead = bytesRead - fileListArray[i].size;
			}
		}
	},
	"uploadError": function(opt) {
		var uploadId = opt.uploadId;
		if (opt.showFileItemProgress) {
			$("#" + uploadId + " .box .fileItem .progress>div").addClass("error");
			$("#" + uploadId + " .box .fileItem .progress>div").css("width", "100%");
		}
		
		var progressBar = $("#" + uploadId + " .subberProgress .progress>div");
		progressBar.css("width", "0%");
		progressBar.html("0%");
	},
	"getFilesDataAmount": function(opt) {
		var fileList = uploadFileList.getFileList(opt);
		var summer = 0;
		for (var i = 0; i < fileList.length; i++) {
			var fileItem = fileList[i];
			if (fileItem != null)
				summer = parseFloat(summer) + fileItem.size;
		}
		return summer;
	},
	"startUpload": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId).attr("isUpload", "true")
	},
	"stopUpload": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId).removeAttr("isUpload");
	},
	"uploadFile": function(opt) {
		uploadTools.startUpload(opt);
		var uploadUrl = opt.uploadUrl;
		var fileList = uploadFileList.getFileList(opt);
		var rememberFile = [];
		var formData = new FormData();
		var fileNumber = uploadTools.getFileNumber(opt);
		if (fileNumber <= 0) {
			return;
		}
		for (var i = 0; i < fileList.length; i++) {
			if (fileList[i] != null) {
				formData.append("file", fileList[i]);
				rememberFile[rememberFile.length] = fileList[i];
			}
		}
		if (opt.otherData != null && opt.otherData != "") {
			for (var j = 0; j < opt.otherData.length; j++) {
				formData.append(opt.otherData[j].name, opt.otherData[j].value);
			}
		}
		formData.append("filelSavePath", opt.filelSavePath);
		if (uploadUrl != "#" && uploadUrl != "") {
			uploadTools.disableFileUpload(opt);
			uploadTools.disableCleanFile(opt);
			uploadTools.disableFileSelect(opt);
			$.ajax({
				type: "post",
				url: uploadUrl,
				data: formData,
				processData: false,
				contentType: false,
				success: function(data) {
					uploadFileList.flushRememberFile(rememberFile, opt);
					setTimeout(function() {
						opt.onUpload(opt, data)
					}, 500);
					if (!opt.showSummerProgress && opt.isAutoClean) {
						setTimeout(function() {
							uploadEvent.cleanFileEvent(opt);
						}, 2000);
					}
				},
				error: function(e) {
					uploadTools.uploadError(opt);
				}
			});
		} else {
			uploadTools.disableFileSelect(opt);
			uploadTools.disableFileUpload(opt);
			uploadTools.disableCleanFile(opt);
			uploadFileList.flushRememberFile(rememberFile, opt);
		}
		uploadTools.getFileUploadPregressMsg(opt);
	},
	"getFileUploadPregressMsg": function(opt) {
		var uploadId = opt.uploadId;
		var progressUrl = opt.progressUrl;
		if (opt.showSummerProgress) {
			$("#" + uploadId + " .subberProgress").css("display", "block");
		}
		$("#" + uploadId + " .box .fileItem .status>i").removeClass();
		if (progressUrl != "#" && progressUrl != "") {
			var intervalId = setInterval(function() {
				$.get(progressUrl, {}, function(data, status) {
					var percent = data.percent;
					var bytesRead = data.bytesRead;
					if (percent >= 100) {
						clearInterval(intervalId);
						percent = 100;
						uploadTools.initWithCleanFile(opt);
					}
					uploadTools.showUploadProgress(opt, bytesRead, percent);
				}, "json");
			}, 500);
		} else {
			if (opt.velocity == null || opt.velocity == "" || opt.velocity <= 0) {
				opt.velocity = 1;
			}
			var filesDataAmount = uploadTools.getFilesDataAmount(opt);
			var percent = 0;
			var bytesRead = 0;
			var intervalId = setInterval(function() {
				bytesRead += 5000 * parseFloat(opt.velocity);
				if (!opt.scheduleStandard) {
					percent = bytesRead / filesDataAmount * 100;
					percent = percent.toFixed(2);
					if (percent >= 100) {
						clearInterval(intervalId);
						percent = 100;
						uploadTools.initWithCleanFile(opt);
					}
				} else {
					percent += parseFloat(opt.velocity);
					if (percent >= 100) {
						clearInterval(intervalId);
						percent = 100;
						uploadTools.initWithCleanFile(opt);
					}
				}
				uploadTools.showUploadProgress(opt, bytesRead, percent);
			}, 500);
		}
	},
	"disableFileSelect": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId + " .uploadBts .selectFileBt").css("background-color", "#DDDDDD");
		$("#" + uploadId + " .uploadBts .selectFileBt").off();
	},
	"disableFileUpload": function(opt) {
		if (!opt.isHiddenUploadBt) {
			var uploadId = opt.uploadId;
			$("#" + uploadId + " .uploadBts .uploadFileBt").off();
			$("#" + uploadId + " .uploadBts .uploadFileBt i").css("color", "#DDDDDD");
		}
	},
	"disableCleanFile": function(opt) {
		if (!opt.isHiddenCleanBt) {
			var uploadId = opt.uploadId;
			$("#" + uploadId + " .uploadBts .cleanFileBt").off();
			$("#" + uploadId + " .uploadBts .cleanFileBt i").css("color", "#DDDDDD");
		}
	},
	"getFileNumber": function(opt) {
		var number = 0;
		var fileList = uploadFileList.getFileList(opt);
		for (var i = 0; i < fileList.length; i++) {
			if (fileList[i] != null) {
				number++;
			}
		}
		return number;
	},
	"getFileNameWithUrl": function(fileUrl) {
		var index = fileUrl.lastIndexOf("/");
		if (index <= 0) {
			index = fileUrl.lastIndexOf("\\");
		}
		index = index + 1;
		var fileName = fileUrl.substring(index, fileUrl.length);
		return fileName;
	},
	"flushOpt": function(opt) {
		var uploadId = opt.uploadId;
		$("#" + uploadId).data("opt", opt);
	},
	"getOpt": function(uploadId) {
		var opt = $("#" + uploadId).data("opt");
		return opt;
	},
	"showFileResult": function(uploadId, fileUrl, fileId, deleteFile, deleteEvent) {
		if (fileUrl == null || fileUrl == "" || uploadId == null || uploadId == "") {
			return;
		}
		var boxJsObj = $("#" + uploadId + " .box").get(0);
		var fileName = uploadTools.getFileNameWithUrl(fileUrl);
		var fileType = uploadTools.getSuffixNameByFileName(fileName);
		var isImg = uploadTools.isInArray(fileType, uploadTools.imgArray);
		fileType = fileType.toUpperCase();
		var showTypeStr = "<div class='fileType'>" + fileType + "</div> <i class='iconfont icon-wenjian'></i>";
		if (isImg) {
			if (fileUrl != null && fileUrl != "null" && fileUrl != "") {
				showTypeStr = "<img src='" + fileUrl + "'/>";
			}
		}
		var showImgStyle = "imgShow";
		if (!deleteFile) {
			showImgStyle += " imgShowResult";
		}
		var modelStr = "";
		modelStr += "<div class='fileItem' showFileCode='" + fileId + "'>";
		modelStr += "<div class='" + showImgStyle + "'>";
		modelStr += showTypeStr;
		modelStr += " </div>";
		if (deleteFile) {
			modelStr += "<div class='status'>";
			modelStr += "<i class='iconfont icon-shanchu'></i>";
			modelStr += "</div>";
		}
		modelStr += " <div class='fileName'>";
		modelStr += fileName;
		modelStr += "</div>";
		modelStr += " </div>";
		$(boxJsObj).append(modelStr);
		if (deleteFile) {
			$(".fileItem[showFileCode='" + fileId + "']").mousedown(function() {
				if (deleteEvent != null && deleteEvent != "" && (typeof deleteEvent === "function")) {
					deleteEvent(fileId);
				}
			});
		}
	}
};
var uploadEvent = {
	"dragListingEvent": function(e, opt) {
		if (opt.autoCommit) {
			uploadEvent.cleanFileEvent(opt);
		}
		e.preventDefault();
		var fileList = e.dataTransfer.files;
		uploadTools.addFileList(fileList, opt);
		if (opt.autoCommit) {
			uploadEvent.uploadFileEvent(opt);
		}
	},
	"deleteFileEvent": function(opt, obj) {
		var fileItem = $(obj).parent().parent();
		var fileCodeId = fileItem.attr("fileCodeId");
		var fileListArray = uploadFileList.getFileList(opt);
		delete fileListArray[fileCodeId];
		uploadFileList.setFileList(fileListArray, opt);
		fileItem.remove();
	},
	"selectFileEvent": function(opt) {
		var uploadId = opt.uploadId;
		var ismultiple = opt.ismultiple;
		var inputObj = document.createElement('input');
		inputObj.setAttribute('id', uploadId + '_file');
		inputObj.setAttribute('type', 'file');
		inputObj.setAttribute("style", 'visibility:hidden');
		if (ismultiple) {
			inputObj.setAttribute("multiple", "multiple");
		}
		$(inputObj).on("change", function() {
			uploadEvent.selectFileChangeEvent(this.files, opt);
		});
		document.body.appendChild(inputObj);
		inputObj.click();
	},
	"selectFileChangeEvent": function(files, opt) {
		uploadTools.addFileList(files, opt);
		uploadTools.cleanFilInputWithSelectFile(opt);
		if (opt.autoCommit) {
			uploadEvent.uploadFileEvent(opt);
		}
	},
	"uploadFileEvent": function(opt) {
		uploadTools.flushOpt(opt);
		if (opt.beforeUpload != null && (typeof opt.beforeUpload === "function")) {
			opt.beforeUpload(opt);
		}
		uploadTools.uploadFile(opt);
	},
	"cleanFileEvent": function(opt) {
		var uploadId = opt.uploadId;
		if (opt.showSummerProgress) {
			$("#" + uploadId + " .subberProgress").css("display", "none");
			$("#" + uploadId + " .subberProgress .progress>div").css("width", "0%");
			$("#" + uploadId + " .subberProgress .progress>div").html("0%");
		}
		uploadTools.cleanFilInputWithSelectFile(opt);
		uploadFileList.setFileList([], opt);
		$("#" + uploadId + " .box .fileItem[filecodeid]").remove();
		uploadTools.initWithUpload(opt);
		uploadTools.initWithSelectFile(opt);
		uploadTools.stopUpload(opt);
	}
};
var uploadFileList = {
	"initFileList": function(opt) {
		opt.fileList = new Array();
	},
	"getFileList": function(opt) {
		return opt.fileList;
	},
	"setFileList": function(fileList, opt) {
		opt.fileList = fileList;
		uploadTools.flushOpt(opt);
	},
	"flushRememberFile": function(fileList, opt) {
		if (opt.rememberUpload) {
			if (opt.rememberFile == null || opt.rememberFile == "" || opt.rememberFile.length == 0) {
				opt.rememberFile = opt.fileList;
			} else {
				var rememberFileArray = opt.rememberFile;
				for (var i = 0; i < fileList.length; i++) {
					rememberFileArray[rememberFileArray.length] = fileList[i];
				}
				opt.rememberFile = rememberFileArray;
				alert(opt.rememberFile.length);
			}
		}
	}
};
var formTake = {
	"getData": function(formId) {
		var formData = {};
		var $form = $("#" + formId);
		var input_doms = $form.find("input[name][ignore!='true'],textarea[name][ignore!='true']");
		var select_doms = $form.find("select[name][ignore!='true']");
		for (var i = 0; i < input_doms.length; i++) {
			var inputItem = input_doms.eq(i);
			var inputName = "";
			if (inputItem.attr("type") == "radio") {
				if (inputItem.is(":checked")) {
					inputName = inputItem.attr("name");
					formData[inputName] = $.trim(inputItem.val());
				}
			} else {
				inputName = inputItem.attr("name");
				formData[inputName] = $.trim(inputItem.val());
			}
		}
		for (var j = 0; j < select_doms.length; j++) {
			var selectItem = select_doms.eq(j);
			var selectName = selectItem.attr("name");
			formData[selectName] = $.trim(selectItem.val());
		}
		return formData;
	},
	"getDataWithUploadFile": function(formId) {
		var formData = [];
		var $form = $("#" + formId);
		var input_doms = $form.find("input[name][ignore!='true'],textarea[name][ignore!='true']");
		var select_doms = $form.find("select[name][ignore!='true']");
		for (var i = 0; i < input_doms.length; i++) {
			var inputItem = input_doms.eq(i);
			var inputName = "";
			if (inputItem.attr("type") == "radio") {
				if (inputItem.is(":checked")) {
					inputName = inputItem.attr("name");
					formData[formData.length] = {
						"name": inputName,
						"value": $.trim(inputItem.val())
					}
				}
			} else {
				inputName = inputItem.attr("name");
				formData[formData.length] = {
					"name": inputName,
					"value": $.trim(inputItem.val())
				}
			}
		}
		for (var j = 0; j < select_doms.length; j++) {
			var selectItem = select_doms.eq(j);
			var selectName = selectItem.attr("name");
			formData[formData.length] = {
				"name": selectName,
				"value": $.trim(selectItem.val())
			}
		}
		return formData;
	}
};
