(function ($) {
    $.fn.validateForm = function (options) {

        const defaultMessages = {
            tr: {
                required: "{field} alanı boş bırakılamaz.",
                email: "Geçerli bir e-posta adresi giriniz.",
                numberLength: "{field} {length} haneli olmalıdır.",
                phone: "Telefon numarası 11 haneli olmalıdır.",
                creditcard: "Kredi kartı numarası 16 haneli olmalıdır.",
                success: "İşlem başarıyla gerçekleşti!",
                fail: "Bir hata oluştu!"
            },
            en: {
                required: "{field} field is required.",
                email: "Please enter a valid email address.",
                numberLength: "{field} must be {length} digits.",
                phone: "Phone number must be 11 digits.",
                creditcard: "Credit card number must be 16 digits.",
                success: "Form submitted successfully!",
                fail: "An error occurred while submitting the form!"
            }
        };

        const settings = $.extend({
            lang: "tr",
            messages: defaultMessages,
            onSuccess: null,
            onError: null
        }, options);

        let currentLang = settings.lang;

        function getMessage(key, vars = {}) {
            let msg = settings.messages[currentLang][key] || "";
            for (let k in vars) msg = msg.replace(`{${k}}`, vars[k]);
            return msg;
        }

        function formatPhone(val) {
            val = val.replace(/\D/g, "");
            if (val.length === 10 && val.startsWith("5")) val = "0" + val;
            if (val.length > 11) val = val.substr(0, 11);
            let formatted = "";
            if (val.length > 0) formatted = val.substr(0, 4);
            if (val.length > 4) formatted += " " + val.substr(4, 3);
            if (val.length > 7) formatted += " " + val.substr(7, 2);
            if (val.length > 9) formatted += " " + val.substr(9, 2);
            return formatted;
        }

        function formatCreditCard(val) {
            val = val.replace(/\D/g, "");
            if (val.length > 16) val = val.substr(0, 16);
            return val.replace(/(.{4})/g, "$1 ").trim();
        }

        function validateInput($input) {
            const tag = $input.prop("tagName").toLowerCase();
            let value = $input.val().trim();
            const required = $input.data("required");
            const type = $input.data("type");

            let $error = $input.siblings(".error-msg");
            if ($error.length === 0) {
                $error = $('<div class="error-msg"></div>');
                $input.after($error);
            }
            $error.hide();

            let labelText = $input.prev("label").text().trim() || $input.attr("placeholder") || "Field";
            $input.removeClass("error");

            if (required && (value === "" || (tag === "select" && (value === "0" || value === "-1")))) {
                $input.addClass("error");
                $error.text(getMessage("required", { field: labelText })).show();
                return false;
            }

            if (type === "email" && value !== "") {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    $input.addClass("error");
                    $error.text(getMessage("email")).show();
                    return false;
                }
            }

            if (type && type.startsWith("number_") && value !== "") {
                const maxLen = parseInt(type.split("_")[1]);
                if (value.replace(/\D/g, '').length !== maxLen) {
                    $input.addClass("error");
                    $error.text(getMessage("numberLength", { field: labelText, length: maxLen })).show();
                    return false;
                }
            }

            if (type === "phone" && value !== "") {
                const digits = value.replace(/\D/g, '');
                if (digits.length !== 11) {
                    $input.addClass("error");
                    $error.text(getMessage("phone")).show();
                    return false;
                }
            }

            if (type === "creditcard" && value !== "") {
                const digits = value.replace(/\D/g, '');
                if (digits.length !== 16) {
                    $input.addClass("error");
                    $error.text(getMessage("creditcard")).show();
                    return false;
                }
            }

            return true;
        }

        this.each(function () {
            const $form = $(this);

            // input değişiklikleri
            $form.on("input change", "input, textarea, select", function () {
                const $input = $(this);
                const type = $input.data("type");
                let value = $input.val();

                if ($input.is("input")) {
                    if (type === "text") $input.val(value.replace(/[0-9]/g, ""));
                    if (type === "number") $input.val(value.replace(/\D/g, ""));
                    if (type && type.startsWith("number_")) {
                        const maxLen = parseInt(type.split("_")[1]);
                        value = value.replace(/\D/g, "").substr(0, maxLen);
                        $input.val(value);
                    }
                    if (type === "phone") $input.val(formatPhone(value));
                    if (type === "creditcard") $input.val(formatCreditCard(value));
                }

                validateInput($input);
            });

            // blur kontrol
            $form.on("blur", "input, textarea, select", function () {
                validateInput($(this));
            });

            // sayfa yüklenince autofill varsa formatla
            $form.find("input[data-type='phone'], input[data-type='creditcard']").each(function () {
                const $input = $(this);
                const type = $input.data("type");
                const value = $input.val();
                if (value) {
                    if (type === "phone") $input.val(formatPhone(value));
                    if (type === "creditcard") $input.val(formatCreditCard(value));
                }
            });

            // submit
            $form.on("submit", function (e) {
                e.preventDefault();

                let isValid = true;
                $form.find("input, textarea, select").each(function () {
                    if (!validateInput($(this))) isValid = false;
                });

                if (!isValid) {
                    if (settings.onError) settings.onError();
                    return false;
                }

                // Submit butonunu disable et
                const $submitBtn = $form.find("button[type=submit], input[type=submit]");
                $submitBtn.prop("disabled", true).addClass("loading");

                $.ajax({
                    url: $form.attr("action"),
                    type: $form.attr("method") || "POST",
                    data: $form.serialize(),
                    success: function (response) {
                        showCenterToast(getMessage("success"), "success");
                        $form[0].reset();
                        $form.find(".error-msg").hide();
                        if (settings.onSuccess) settings.onSuccess(response);
                    },
                    error: function () {
                        showCenterToast(getMessage("fail"), "error");
                        if (settings.onError) settings.onError();
                    },
                    complete: function () {
                        // İşlem bittiğinde butonu tekrar aktif et
                        $submitBtn.prop("disabled", false).removeClass("loading");
                    }
                });
            });
        });

        return {
            setLang: function (lang) {
                if (settings.messages[lang]) currentLang = lang;
            }
        };
    };
})(jQuery);
