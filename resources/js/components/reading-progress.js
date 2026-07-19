import {Component} from './component';

export class ReadingProgress extends Component {

    setup() {
        this.container = this.$el;
        this.pageId = this.$opts.pageId;
        this.progressBar = document.querySelector('[data-reading-progress-bar]');
        this.progressText = document.querySelector('[data-reading-progress-text]');
        this.progressBadge = document.querySelector('[data-reading-progress-badge]');
        this.lastSavedProgress = null;
        this.lastSavedAt = 0;
        this.pendingSave = false;
        this.restoreProgress();
        this.bindEvents();
    }

    bindEvents() {
        let ticking = false;
        const onScroll = () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    this.updateProgress();
                    ticking = false;
                });
                ticking = true;
            }
        };

        window.addEventListener('scroll', onScroll, {passive: true});
        window.addEventListener('beforeunload', () => this.saveProgress(true));
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') {
                this.saveProgress(true);
            }
        });
    }

    async restoreProgress() {
        try {
            const response = await window.$http.get(`/reading-progress/${this.pageId}`);
            const data = response.data?.data;
            if (data && typeof data.progress_percentage === 'number') {
                this.setProgress(data.progress_percentage, false);
                if (typeof data.scroll_position === 'number') {
                    window.scrollTo(0, data.scroll_position);
                }
            }
        } catch (error) {
            // Ignore restore failures silently.
        }
    }

    updateProgress() {
        const scrollTop = window.scrollY || window.pageYOffset || 0;
        const documentHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
        const windowHeight = window.innerHeight;
        const maxScroll = Math.max(1, documentHeight - windowHeight);
        const percentage = Math.min(100, Math.max(0, Math.round((scrollTop / maxScroll) * 100)));

        this.setProgress(percentage, true);
        this.scheduleSave(percentage, scrollTop);
    }

    setProgress(percentage, shouldUpdateUi = true) {
        if (shouldUpdateUi) {
            if (this.progressBar) {
                this.progressBar.style.width = `${percentage}%`;
            }
            if (this.progressText) {
                this.progressText.textContent = `${percentage}%`;
            }
            if (this.progressBadge) {
                if (percentage >= 100) {
                    this.progressBadge.hidden = false;
                    this.progressBadge.textContent = 'Completed ✓';
                } else {
                    this.progressBadge.hidden = true;
                    this.progressBadge.textContent = '';
                }
            }
        }
    }

    scheduleSave(percentage, scrollTop) {
        const now = Date.now();
        const shouldSaveByDelta = this.lastSavedProgress === null || Math.abs(percentage - this.lastSavedProgress) >= 5;
        const shouldSaveByTimeout = now - this.lastSavedAt >= 10000;

        if (!shouldSaveByDelta && !shouldSaveByTimeout) {
            return;
        }

        if (this.pendingSave) {
            return;
        }

        this.pendingSave = true;
        this.saveProgress(false, percentage, scrollTop).finally(() => {
            this.pendingSave = false;
        });
    }

    async saveProgress(force = false, percentage = null, scrollTop = null) {
        const currentPercentage = percentage ?? this.lastSavedProgress ?? 0;
        const currentScroll = scrollTop ?? Math.max(0, window.scrollY || window.pageYOffset || 0);

        if (!force && this.lastSavedProgress !== null && Math.abs(currentPercentage - this.lastSavedProgress) < 5 && Date.now() - this.lastSavedAt < 10000) {
            return;
        }

        try {
            await window.$http.post(`/reading-progress/${this.pageId}`, {
                progress_percentage: currentPercentage,
                scroll_position: Math.round(currentScroll),
            });
            this.lastSavedProgress = currentPercentage;
            this.lastSavedAt = Date.now();
        } catch (error) {
            // Ignore save failures silently.
        }
    }
}
