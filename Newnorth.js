if(typeof Newnorth === "undefined") {
	Newnorth = {};
}

/* Newnorth.Event
/* * * * */

Newnorth.Event = function() {
	this.ivListeners = [];
};

Newnorth.Event.prototype.AddListener = function(pvObject, pvMethod) {
	this.ivListeners.push({
		ivObject: pvObject,
		ivMethod: pvMethod,
	});
};

Newnorth.Event.prototype.Invoke = function(pvInvoker, pvData) {
	for(var i = 0; i < this.ivListeners.length; ++i) {
		this.ivListeners[i].ivMethod.call(
			this.ivListeners[i].ivObject,
			pvInvoker,
			pvData
		);
	}
};

/* Newnorth.Controls
/* * * * */

Newnorth.Controls = {};

Newnorth.Controls.Find = function(pvParent, pvType) {
	var elements = pvParent.getElementsByTagName("*");

	for(var i = 0; i < elements.length; ++i) {
		var type = elements[i].getAttribute("Newnorth:Control");

		if(type === pvType) {
			if(!elements[i].ivIsControl) {
				Newnorth.Controls[type].call(elements[i]);
			}

			return elements[i];
		}
	}

	return null;
}

Newnorth.Controls.Control = function() {
	this.ivIsControl = true;
};

Newnorth.Controls.ErrorMessagesControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivErrorMessages = [];

	this.HasErrorMessage = function(pvText) {
		for(var i = 0; i < this.ivErrorMessages.length; ++i) {
			if(this.ivErrorMessages[i].ivText === pvText) {
				return true;
			}
		}

		return false;
	};

	this.AddErrorMessage = function(pvText) {
		if(this.HasErrorMessage(pvText)) {
			return;
		}

		var element = document.createElement("p");
		element.innerHTML = pvText;

		this.appendChild(element);

		this.style.display = "block";

		this.ivErrorMessages.push({ivElement: element, ivText: pvText});
	};

	this.RemoveErrorMessage = function(pvText) {
		for(var i = 0; i < this.ivErrorMessages.length; ++i) {
			if(this.ivErrorMessages[i].ivText !== pvText) {
				continue;
			}

			this.removeChild(this.ivErrorMessages[i].ivElement);

			if(this.childNodes.length === 0) {
				this.style.display = "none";
			}

			this.ivErrorMessages.splice(i, 1);

			return;
		}
	};
};

Newnorth.Controls.ValidatableControl = function() {
	this.ivIsValidatable = true;

	this.ivIsValid = true;

	this.ivIsDefaultValue = true;

	this.ivValidators = [];

	this.ivErrorMessages = Newnorth.Controls.Find(this.ivContainer === undefined ? this : this.ivContainer, "ErrorMessagesControl");

	this.AddValidator = function(pvMethod, pvErrorMessage) {
		if(this.ivIsValid && !pvMethod.call(this)) {
			this.ivIsValid = false;

			if(this.ivForm !== undefined) {
				this.ivForm.ivIsValid = false;
			}
		}

		this.ivValidators.push({ivMethod: pvMethod, ivErrorMessage: pvErrorMessage});
	};

	this.Validate = function() {
		this.ivIsValid = true;

		if(this.ivIsDefaultValue || this.ivErrorMessages === null) {
			for(var i = 0; i < this.ivValidators.length; ++i) {
				if(!this.ivValidators[i].ivMethod.call(this)) {
					this.ivIsValid = false;
				}
			}
		}
		else {
			for(var i = 0; i < this.ivValidators.length; ++i) {
				if(this.ivValidators[i].ivMethod.call(this)) {
					this.ivErrorMessages.RemoveErrorMessage(this.ivValidators[i].ivErrorMessage);
				}
				else {
					this.ivErrorMessages.AddErrorMessage(this.ivValidators[i].ivErrorMessage);

					this.ivIsValid = false;
				}
			}
		}
	};
};

Newnorth.Controls.FormControl = function() {
	this.ivIsValid = true;

	this.ivOnEnterValidStateEvent = new Newnorth.Event();

	this.ivOnEnterInvalidStateEvent = new Newnorth.Event();

	this.ivControls = [];

	this.ivOnAnyValueChangedEvent = new Newnorth.Event();

	{
		var anyValueChangedEvent = this.getAttribute("Newnorth:AnyValueChangedEvent");

		if(anyValueChangedEvent !== null) {
			this.ivOnAnyValueChangedEvent.AddListener(this, new Function(["pvControl", "pvValue"], anyValueChangedEvent));
		}
	}

	this.AddControl = function(pvControl) {
		if(pvControl.ivIsValidatable === true) {
			this.ivIsValid = this.ivIsValid && pvControl.ivIsValid;
		}

		this.ivControls.push(pvControl);
	};

	this.Validate = function() {
		var isValid = true;

		for(var i = 0; i < this.ivControls.length; ++i) {
			if(this.ivControls[i].Validate !== undefined) {
				this.ivControls[i].Validate();

				isValid = isValid && this.ivControls[i].ivIsValid;
			}
		}

		if(this.ivIsValid !== isValid) {
			this.ivIsValid = isValid;
			
			if(this.ivIsValid) {
				this.ivOnEnterValidStateEvent.Invoke(this, null);
			}
			else {
				this.ivOnEnterInvalidStateEvent.Invoke(this, null);
			}
		}
	};

	this.GetValuesAsString = function() {
		var values = "";

		for(var i = 0; i < this.ivControls.length; ++i) {
			if(0 < this.ivControls[i].name.length) {
				values += "&" + this.ivControls[i].name + "=" + encodeURIComponent(this.ivControls[i].GetValue());
			}
		}

		return values.substring(1);
	};

	this.onsubmit = function() {
		for(var i = 0; i < this.ivControls.length; ++i) {
			this.ivControls[i].ivIsDefaultValue = false;
		}

		this.Validate();

		return this.ivIsValid;
	};
};

Newnorth.Controls.HiddenControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivForm = this.form;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivForm.AddControl(this);
};

Newnorth.Controls.TextBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.TextAreaBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.PasswordBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.value;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.value) {
			this.ivValue = this.value;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.DropDownListControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivValue = this.selectedIndex;

	this.GetValue = function() {
		return this.options[this.selectedIndex].value;
	};

	this.SetValue = function(pvValue) {
		for(var i = 0; i < this.options.length; ++i) {
			if(this.options[i].value === pvValue) {
				this.selectedIndex = i;

				return;
			}
		}

		throw new Error("Invalid value.");
	};

	this.ivOnValueChanged = new Newnorth.Event();

	var isValueChangedMethod = function() {
		if(this.ivValue !== this.selectedIndex) {
			this.ivValue = this.selectedIndex;

			this.ivIsDefaultValue = false;

			this.ivOnValueChanged.Invoke(this, this.options[this.selectedIndex].value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.options[this.selectedIndex].value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("change", isValueChangedMethod);

	this.addEventListener("keydown", isValueChangedMethod);

	this.addEventListener("keyup", isValueChangedMethod);

	this.ivForm.AddControl(this);
};

Newnorth.Controls.CheckBoxControl = function() {
	Newnorth.Controls.Control.call(this);

	this.ivContainer = this.parentNode.parentNode;

	Newnorth.Controls.ValidatableControl.call(this);

	this.ivForm = this.form;

	this.ivChecked = this.checked;

	this.GetValue = function() {
		return this.value;
	};

	this.SetValue = function(pvValue) {
		this.value = pvValue;
	};

	this.ivOnIsCheckedChanged = new Newnorth.Event();

	var isIsCheckedChangedMethod = function() {
		if(this.ivChecked !== this.checked) {
			this.ivChecked = this.checked;

			this.ivIsDefaultValue = false;

			this.ivOnIsCheckedChanged.Invoke(this, this.value);

			if(this.ivForm === null) {
				this.Validate();
			}
			else {
				this.ivForm.ivOnAnyValueChangedEvent.Invoke(this, this.value);

				this.ivForm.Validate();
			}
		}
	};

	this.addEventListener("click", isIsCheckedChangedMethod);

	this.addEventListener("keydown", isIsCheckedChangedMethod);

	this.addEventListener("keyup", isIsCheckedChangedMethod);

	this.ivForm.AddControl(this);
};

window.addEventListener(
	"load",
	function() {
		var elements = document.getElementsByTagName("*");

		for(var i = 0; i < elements.length; ++i) {
			var type = elements[i].getAttribute("Newnorth:Control");

			if(type === null) {
				continue;
			}

			if(Newnorth.Controls[type] === undefined) {
				throw new Error("There's no control of the type \"" + type + "\".");
			}

			Newnorth.Controls[type].call(elements[i]);
		}
	}
);