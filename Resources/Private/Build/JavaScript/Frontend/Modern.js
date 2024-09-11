import { load } from '@fingerprintjs/botd';

(async () => {
  const botDetection = await load({ monitoring: false });
  const { bot: isBot } = await botDetection.detect();

  if (!isBot) {
    await import('./Lux');
  }
})();
