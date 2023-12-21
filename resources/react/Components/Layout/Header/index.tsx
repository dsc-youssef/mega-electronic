// Dependencies
import { FC } from "react";

// Redux
import { useSelector } from "react-redux";
import { RootState } from "@/redux/store";

// Types
import { HeaderProps } from "@/types/Components/Layout/Header";
import { Link } from "@inertiajs/react";

const Header: FC<HeaderProps> = ({ title, children }) => {
  const words = useSelector((state: RootState) => state.layout.layoutsWords.dashboard);

  return (
    <header className={"pf-header"}>
      <h3 className={"pf-header-title"}>{title}</h3>
      <div className={'pf-header-buttons'}>
        <button className={"btn btn-outline-primary btn-icon"} onClick={() => window.history.back()}>
          <i className={"fal fa-angle-left"} />
          <span>{words?.back}</span>
        </button>
        <Link href={route('sales.create.show')}>
          <button className={"btn btn-success btn-icon"}>
            <i className={"fal fa-cart-plus "} />
            <span>{words?.new_sale}</span>
          </button>
        </Link>
        {children}
      </div>
    </header>
  )
}

export default Header;