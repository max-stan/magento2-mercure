function reconnect() {
    if (eventSource) {
        eventSource.close();
    }

    if (!topics.size || !authorization) {
        return;
    }

    // Build URL with all topics
    const url = new URL(`${self.location.origin}/.well-known/mercure`);
    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    });
    url.searchParams.append('authorization', authorization);

    // Create new connection (no cookies — query param auth)
    eventSource = new EventSource(url.toString());

    eventSource.onopen = () => console.info('Mercure connected to topics: ', Array.from(topics));
    eventSource.onerror = (error) => console.error('Mercure connection error:', error);

    eventSource.onmessage = (event) => {
        const { data: raw } = event,
            data = JSON.parse(raw);
        console.log('Mercure message received', data);
        connections.forEach(connection => connection.postMessage(data))
    };
}

let eventSource = null,
    authorization = null;
const topics = new Set(),
    connections = new Set();

onconnect = (e) => {
    const port = e.ports[0];
    connections.add(port);
    console.info(`Admin Worker has been started, timestamp: ${Date.now()}`, port)

    port.addEventListener("message", (e) => {
        const { data } = e;
        console.info('Message has been received', data);

        if (data.type === 'authorize') {
            authorization = data.token;
            reconnect();
        }

        if (data.type === 'subscribe') {
            data.topics.forEach(topic => topics.add(topic));
            reconnect();
        }
    });

    port.start();
};
