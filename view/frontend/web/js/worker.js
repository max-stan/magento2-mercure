function reconnect() {
    if (eventSource) {
        eventSource.close();
    }

    if (!topics.size) {
        return;
    }

    // Build URL with all topics
    const url = new URL(hubUrl);
    topics.forEach(topic => {
        url.searchParams.append('topic', topic);
    });

    // Create new connection
    eventSource = new EventSource(url.toString(), {
        withCredentials: true
    });

    eventSource.onopen = ()=> console.info('Mercure connected to topics: ', Array.from(topics));
    eventSource.onerror = (error) => console.error('Mercure connection error:', error);

    eventSource.onmessage = (event) => {
        const { data: raw } = event,
            data = JSON.parse(raw);
        console.log('Mercure message received', data);
        connections.forEach(connection => connection.postMessage(data))
    };
}

let eventSource = null;
const topics = new Set(),
    connections = new Set();

const params = new URLSearchParams(self.location.search),
    hubUrl = atob(params.get('hub'));

onconnect = (e) => {
    const port = e.ports[0];
    connections.add(port);
    console.info(`Chat Worker has been started, timestamp: ${Date.now()}`, port)

    port.addEventListener("message", (e) => {
        const { data } = e;
        console.info('Message has been received', data);

        if (data.type === 'subscribe') {
            data.topics.forEach(topic => topics.add(topic));
            reconnect();
        }
    });

    port.start();
};
