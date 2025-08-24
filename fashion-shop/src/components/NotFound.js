import Lottie from "lottie-react";
import notFoundAnimation from "../animation/404.json";

export default function NotFound() {
  return (
    <div style={{  marginLeft:"20%" }}>
      <Lottie
         animationData={notFoundAnimation}
         autoplay
        loop 
         style={{ width: 1000, height: 500 }}
      />
     
    </div>
  );
}
