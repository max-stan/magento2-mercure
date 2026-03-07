function reconnect() {
    if (eventSource) {
        eventSource.close();
    }

    if (!topics.size || !authorizationToken) {
        return;
    }

    // Build URL with all topics
    const url = new URL(hubUrl);
    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    });

    url.searchParams.append('authorization', authorizationToken);

    // Create new connection
    eventSource = new EventSource(url.toString());

    eventSource.onopen = ()=> console.info('Mercure connected to topics: ', Array.from(topics));
    eventSource.onerror = (error) => console.error('Mercure connection error:', error, url);

    eventSource.onmessage = (event) => {
        const { data: raw } = event,
            data = JSON.parse(raw);
        connections.forEach(connection => connection.postMessage(data))
    };
}

let eventSource = null,
    authorizationToken = null;

const topics = new Set(),
    connections = new Set(),
    params = new URLSearchParams(self.location.search),
    hubUrl = atob(params.get('hub'));

onconnect = (e) => {
    const port = e.ports[0];
    connections.add(port);

    port.addEventListener("message", (e) => {
        const { data } = e;
        if (data.type === 'subscribe') {
            data.topics.forEach(topic => topics.add(topic));
            reconnect();
        }

        if (data.type === 'authorization') {
            authorizationToken = data.token;
            reconnect();
        }
    });

    port.start();
};
