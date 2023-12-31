// Dependencies
import { FC, useEffect } from "react";

// Hooks
import useToast from "@/hooks/useToast";
import { usePage } from "@inertiajs/react";

// Components
import Head from "@/Components/Layout/Head";
import Sidebar from "@/Components/Layout/Sidebar";
import Navbar from "@/Components/Layout/Navbar";
import Footer from "@/Components/Layout/Footer";
import Cart from "@/Components/Layout/Cart";


// Types
import { LayoutProps } from "@/types/Layouts/Layout";
import { Container } from "react-bootstrap";

const Dashboard: FC<LayoutProps> = ({ children, pageTitle }) => {
  const { settings, notification } = usePage().props as ServerProps;
  const { toast } = useToast();

  useEffect(() => {
    const body: HTMLBodyElement = document.querySelector('body') as HTMLBodyElement;
    body.classList.add(settings.dark_mode ? "dark" : "light");

  }, [settings]);

  // Set notification
  useEffect(() => {
    if (notification) {
      toast(notification.message, notification.type);
    }
  }, [notification]);

  return (
    <>
      <Head title={`Dashboard - ${pageTitle}`}></Head>
      <main className={`pf-dashboard-layout  dir-${settings.direction}`}>
        <Cart />
        <Sidebar />
        <div className="pf-dashboard-layout-main-content">
          <Navbar />
          <Container className="pf-dashboard-layout-page-content">
            {children}
          </Container>
          <Footer />
        </div>
      </main>
    </>
  )
}

export default Dashboard;
