(function () {
  const configNode = document.getElementById('callingCrmConfig');

  if (!configNode) {
    return;
  }

  try {
    window.CallingCrmApi = JSON.parse(configNode.textContent);
  } catch (error) {
    console.error('Calling CRM config could not be parsed.', error);
    return;
  }

  window.callingCrmRequest = function (path, options) {
    const url = path.indexOf('http') === 0
      ? path
      : window.CallingCrmApi.baseUrl + '/' + path.replace(/^\/+/, '');
    const requestOptions = Object.assign({
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.CallingCrmApi.csrfToken
      }
    }, options || {});

    if (requestOptions.body && typeof requestOptions.body !== 'string' && !(requestOptions.body instanceof FormData)) {
      requestOptions.body = JSON.stringify(requestOptions.body);
    }

    if (requestOptions.body instanceof FormData) {
      delete requestOptions.headers['Content-Type'];
    }

    return fetch(url, requestOptions).then(async function (response) {
      const payload = await response.json().catch(function () {
        return null;
      });

      if (!response.ok) {
        const error = new Error(payload?.message || 'Calling CRM request failed');
        error.response = response;
        error.payload = payload;
        throw error;
      }

      return payload;
    });
  };
})();
