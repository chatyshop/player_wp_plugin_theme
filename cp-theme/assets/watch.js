document.documentElement.classList.add("cp-theme-no-mini");

const keepPlayerNormal = () => {
  document.querySelectorAll(".cpwp-player.chatyplayer-mini").forEach((player) => {
    player.classList.remove("chatyplayer-mini");
    Object.assign(player.style, {
      position: "",
      left: "",
      right: "",
      top: "",
      bottom: "",
      width: "",
      height: "",
      zIndex: "",
      transform: ""
    });
  });
};

new MutationObserver(keepPlayerNormal).observe(document.documentElement, {
  attributes: true,
  subtree: true,
  attributeFilter: ["class"]
});

keepPlayerNormal();
