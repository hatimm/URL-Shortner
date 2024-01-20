<template>
  <div class="url-shortener">
    <h1 class="title">URL Shortener</h1>
    <form v-if="!redirectMode" @submit.prevent="shortenUrl" class="url-box">
      <label for="longUrl" class="label">Long URL:</label>
      <input v-model="longUrl" type="text" id="longUrl" class="input" required>
      <button type="submit" class="button" :disabled="loading">
        <span v-if="loading">Loading...</span>
        <span v-else>Shorten URL</span>
      </button>
    </form>
    <div v-else>
      <h1>Redirecting...</h1>
    </div>
    <p v-if="shortenedUrl" class="shortened-url">
      Shortened URL: <a :href="shortenedUrl" @click.prevent="handleRedirect" target="_blank">{{ shortenedUrl }}</a>
    </p>
    <div class="shortened-url-list">
      <h2>Previously Shortened URLs</h2>
      <table>
        <thead>
          <tr>
            <th>Long URL</th>
            <th>Short URL</th>
            <th>Click Count</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="url in displayedShortenedUrls" :key="url.id">
            <td>{{ url.long_url }}</td>
            <td>
              <a :href="`http://127.0.0.1:8000/api/${url.short_url}`" target="_blank" @click="handleRedirect(url)">
                {{ url.short_url }}
              </a>
            </td>
            <td>{{ url.click_count }}</td>
          </tr>
        </tbody>
      </table>
      <div class="button-container">
        <button v-if="displayedShortenedUrls.length > 5" @click="showLess" class="show-button show-less-button" style="background-color: #e74c3c; color: #ffffff; margin-right: 5px;">Show Less</button>
        <button v-if="displayedShortenedUrls.length < shortenedUrls.length" @click="showMore" class="show-button" style="margin-left: 5px;">Show More</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      longUrl: '',
      shortenedUrl: null,
      redirectMode: false,
      loading: false,
      shortenedUrls: [],
      displayedShortenedUrls: [],
      showMoreCount: 5,
      urlStats: [],
    };
  },
  computed: {
    showLessButtonVisible() {
      return this.displayedShortenedUrls.length > 5;
    },
  },
  methods: {
    async shortenUrl() {
      try {
        let inputUrl = this.longUrl.trim();
        if (!inputUrl.startsWith('http://') && !inputUrl.startsWith('https://')) {
          inputUrl = `http://${inputUrl}`;
        }

        const response = await fetch('http://127.0.0.1:8000/api/shorten', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ longUrl: inputUrl }),
        });

        if (!response.ok) {
          throw new Error('Error shortening URL');
        }

        const data = await response.json();
        this.shortenedUrl = data.short_url;
        this.longUrl = '';
      } catch (error) {
        console.error(error);
      }
    },

    async handleRedirect(url) {
      const constructedUrl = `http://127.0.0.1:8000/api/${this.shortenedUrl}`;
      console.log(constructedUrl);
      await this.incrementClickCount(url.id);
      await this.fetchShortenedUrls();
      window.open(constructedUrl, '_blank');
    },

    async incrementClickCount(urlId) {
      try {
        const response = await fetch(`http://127.0.0.1:8000/api/increment-click-count/${urlId}`, {
          method: 'POST',
        });

        if (!response.ok) {
          throw new Error('Error incrementing click count');
        }
      } catch (error) {
        console.error(error);
      }
    },

    async fetchShortenedUrls() {
      try {
        const response = await fetch('http://127.0.0.1:8000/api/shortened-urls');
        if (!response.ok) {
          throw new Error('Error fetching shortened URLs');
        }
        const data = await response.json();
        this.shortenedUrls = data.shortenedUrls;
        this.displayedShortenedUrls = this.shortenedUrls.slice(0, this.showMoreCount);
      } catch (error) {
        console.error(error);
      }
    },

    async fetchUrlStats() {
      try {
        const response = await fetch('http://127.0.0.1:8000/url-stats');
        if (!response.ok) {
          throw new Error('Error fetching URL stats');
        }
        const data = await response.json();
        this.urlStats = data.stats;
      } catch (error) {
        console.error('Error fetching URL stats:', error);
      }
    },

    showMore() {
      this.displayedShortenedUrls = this.shortenedUrls.slice(0, this.displayedShortenedUrls.length + this.showMoreCount);
    },

    showLess() {
      const rowsToHide = Math.min(this.showMoreCount, this.displayedShortenedUrls.length);
      this.displayedShortenedUrls = this.displayedShortenedUrls.slice(0, -rowsToHide);
    },
  },
  mounted() {
    this.fetchShortenedUrls();
    if (this.$route.params.shortUrl) {
      this.redirectMode = true;
      this.handleRedirect();
    }
  },
};
</script>

<style scoped>
.url-shortener {
  max-width: 400px;
  margin: 100px auto 0;
  padding: 130px;
  background-color: #f0f0f0;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.title {
  font-size: 40px;
  margin-bottom: 20px;
  color: #2c3e50;
}

.url-box {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.label {
  font-size: 18px;
  color: #34495e;
  margin-bottom: 12px;
}

.input {
  width: calc(100% - 16px);
  padding: 8px;
  margin-bottom: 18px;
  border: 1px solid #bdc3c7;
  border-radius: 4px;
}

.button {
  background-color: #27ae60;
  color: #ffffff;
  padding: 10px 15px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.show-more-button {
  margin-top: 10px;
}

.button:hover {
  background-color: #219d54;
}

.button:disabled {
  cursor: not-allowed;
  background-color: #bdc3c7;
}

.shortened-url-list { 
  margin-top: 20px;
}

.shortened-url-list h2 {
  font-size: 24px;
  color: #2c3e50;
  margin-bottom: 10px;
}

.shortened-url-list table {
  width: 100%;
  border-collapse: collapse;
}

.shortened-url-list th,
.shortened-url-list td { 
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.shortened-url-list th { 
  background-color: #f2f2f2;
}

.shortened-url-list td a { 
  text-decoration: none;
  color: #3498db;
}

.shortened-url-list td a:hover { 
  text-decoration: underline;
}

.shortened-url-list button { 
  background-color: #3498db;
  color: #ffffff;
  padding: 8px;
  border: none;
  cursor: pointer;
}

.shortened-url-list button:disabled { 
  background-color: #bdc3c7;
  cursor: not-allowed;
}

.show-button {
  margin-top: 10px;
  padding: 8px;
  cursor: pointer;
}

.show-less-button {
  background-color: #e74c3c;
  color: #ffffff;  
  margin-left: 5px;  
}
</style>
