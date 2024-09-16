import { defineBuildConfig } from "unbuild";

export default defineBuildConfig({
  entries: [
    {
      builder: "rollup",
      name: "index",
      input: "./JavaScript/Frontend/Modern",
      outDir: "../../Public/JavaScript/Lux/modern",
    },
  ],
  rollup: {
    esbuild: {
      minify: true,
    }
  },
  outDir: "../../Public/JavaScript/Lux/modern",
  clean: true,
  failOnWarn: false,
});
