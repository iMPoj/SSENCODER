const API_URL = 'api.php';

export async function fetchData(action) {
    try {
        const url = new URL(API_URL, window.location.href);
        url.searchParams.set('action', action);
        
        const response = await fetch(url);
        
        // NEW: Catch expired sessions immediately
        if (response.status === 401) {
            alert("Your session has expired. Please log in again.");
            window.location.href = 'login.php';
            return { success: false, message: "Session expired.", data: [] };
        }

        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            // It returned HTML (likely an error page), stop here to prevent crash
            console.warn(`API returned non-JSON for ${action}:`, await response.text());
            return { success: false, message: "Server error or file not found.", data: [] };
        }

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || `HTTP error! status: ${response.status}`);
        }
        return result;
    } catch (error) {
        console.error(`Could not fetch ${action}:`, error);
        return { success: false, message: error.message, data: [] }; 
    }
}

export async function postData(action, data) {
     try {
        const formData = new FormData();
        formData.append('action', action);

        if (data instanceof FormData) {
            for (const [key, value] of data.entries()) {
                if (key !== 'action') formData.append(key, value);
            }
        } else {
            for (const key in data) {
                formData.append(key, data[key]);
            }
        }
        
        const url = new URL(API_URL, window.location.href);
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        // NEW: Catch expired sessions immediately
        if (response.status === 401) {
            alert("Your session has expired. Please log in again.");
            window.location.href = 'login.php';
            return { success: false, message: "Session expired." };
        }

        // Check if response is JSON before parsing
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            console.warn(`API returned non-JSON for ${action}:`, await response.text());
            return { success: false, message: "Server Error. Check console." };
        }

        const result = await response.json();
        return result;
    } catch (error) {
        console.error(`Could not post ${action}:`, error);
        return { success: false, message: error.message };
    }
}