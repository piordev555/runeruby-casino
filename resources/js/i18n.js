import Vue from 'vue';
import VueI18n from 'vue-i18n';

import { dateTimeFormats } from '../lang/formats/dateTimeFormats';
import { pluralizationRules } from '../lang/formats/pluralization';

Vue.use(VueI18n);

export function loadLocaleMessages() {
    const locales = require.context('../lang', true, /[A-Za-z0-9-_,\s]+\.json$/i);
    const messages = {};
    locales.keys().forEach(key => {
        const matched = key.match(/([A-Za-z0-9-_]+)\./i);
        if (matched && matched.length > 1 && !key.includes('vendor')) {
            const locale = matched[1];
            messages[locale] = locales(key);
        }
    })
    return messages;
}

export const languages = Object.getOwnPropertyNames(loadLocaleMessages());
export const selectedLocale = navigator.language.split('-')[0] || 'en';

export default new VueI18n({
    dateTimeFormats,
    pluralizationRules,
    locale: selectedLocale,
    fallbackLocale: 'en',
    messages: loadLocaleMessages()
});
